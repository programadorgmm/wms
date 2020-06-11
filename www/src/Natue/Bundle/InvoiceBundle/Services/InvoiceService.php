<?php

namespace Natue\Bundle\InvoiceBundle\Services;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Natue\Bundle\InvoiceBundle\Entity\ColumnType\EnumInvoiceStatusType;
use Natue\Bundle\InvoiceBundle\Entity\Invoice;
use Natue\Bundle\InvoiceBundle\Exceptions\MultiplePurchaseOrderOnInvoiceException;
use Natue\Bundle\InvoiceBundle\Repository\InvoiceRepository;
use Natue\Bundle\InvoiceBundle\Taxman\Outcome;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;

/**
 * Class InvoiceManager
 * @package Natue\Bundle\InvoiceBundle\Factories
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class InvoiceService
{
    const DEFAULT_SERIES = 3;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Natue\Bundle\InvoiceBundle\Repository\InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * @var \Natue\Bundle\InvoiceBundle\Services\InvoiceNumberService
     */
    protected $invoiceNumberService;

    /**
     * @var \Natue\Bundle\InvoiceBundle\Services\TaxmanService
     */
    protected $taxmanService;

    /**
     * @param \Doctrine\ORM\EntityManager                               $entityManager
     * @param \Natue\Bundle\InvoiceBundle\Repository\InvoiceRepository  $invoiceRepository
     * @param \Natue\Bundle\InvoiceBundle\Services\InvoiceNumberService $invoiceNumberService
     * @param \Natue\Bundle\InvoiceBundle\Services\TaxmanService        $taxmanService
     */
    public function __construct(
        EntityManager $entityManager,
        InvoiceRepository $invoiceRepository,
        InvoiceNumberService $invoiceNumberService,
        TaxmanService $taxmanService
    ) {
        $this->entityManager        = $entityManager;
        $this->invoiceRepository    = $invoiceRepository;
        $this->invoiceNumberService = $invoiceNumberService;
        $this->taxmanService        = $taxmanService;
    }

    /**
     * @param \Natue\Bundle\StockBundle\Entity\PurchaseOrder                                            $purchaseOrder
     * @param \Doctrine\Common\Collections\Collection|\Natue\Bundle\StockBundle\Entity\StockItem[] $stockItems
     * @return \Natue\Bundle\InvoiceBundle\Entity\Invoice
     * @throws \Natue\Bundle\InvoiceBundle\Exceptions\MultiplePurchaseOrderOnInvoiceException
     */
    public function create(PurchaseOrder $purchaseOrder, Collection $stockItems)
    {
        $number = $this->invoiceNumberService->getAvailableNumber(self::DEFAULT_SERIES);

        $invoice = new Invoice();
        $invoice->setInvoiceNumber($number);
        $invoice->setPurchaseOrder($purchaseOrder);

        foreach ($stockItems as $item) {
            $itsPurchaseOrder = $item->getPurchaseOrderItem()
                ->getPurchaseOrder();

            if ($itsPurchaseOrder->getId() !== $purchaseOrder->getId()) {
                throw new MultiplePurchaseOrderOnInvoiceException;
            }

            $invoice->addItem($item);
        }

        return $invoice;
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Entity\Invoice $invoice
     * @return \Natue\Bundle\InvoiceBundle\Taxman\Invoice
     * @throws \Exception
     */
    public function initialize(Invoice $invoice)
    {
        $invoice->setStatus(EnumInvoiceStatusType::STATUS_INITIALIZED);

        $this->entityManager->persist($invoice);
        $this->entityManager->flush($invoice);

        return $this->taxmanService->issue($invoice);
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Entity\Invoice $invoice
     * @param string                                     $nfeKey
     * @param string                                     $nfeXml
     * @return \Natue\Bundle\InvoiceBundle\Entity\Invoice
     */
    public function confirm(Invoice $invoice, $nfeKey, $nfeXml)
    {
        $invoice->setStatus(EnumInvoiceStatusType::STATUS_CREATED);
        $invoice->setNfeKey($nfeKey);
        $invoice->setNfeXml($nfeXml);

        $this->entityManager->persist($invoice);
        $this->entityManager->flush($invoice);

        return $invoice;
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Entity\Invoice $invoice
     * @param bool                                       $disposeInvoiceNumber
     * @return void
     * @throws \Exception
     */
    public function cancel(Invoice $invoice, $disposeInvoiceNumber = false)
    {
        $invoiceNumber = $invoice->getInvoiceNumber();

        $this->entityManager->remove($invoice);
        $this->entityManager->flush($invoice);

        if ($disposeInvoiceNumber) {
            return;
        }

        $this->invoiceNumberService->recyclable($invoiceNumber);
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Entity\Invoice $invoice
     * @return void
     */
    public function processOutcome(Invoice $invoice)
    {
        $outcome = $this->taxmanService->outcome($invoice);

        switch ($outcome->getStatus()) {
            case Outcome::CREATED:
                $this->confirm($invoice, $outcome->getNfeKey(), $outcome->getNfeXml());
                break;

            case Outcome::DISPOSABLE:
                $this->cancel($invoice, true);
                break;

            default:
            case Outcome::REJECTED:
                $this->cancel($invoice);
                break;
        }
    }
}
