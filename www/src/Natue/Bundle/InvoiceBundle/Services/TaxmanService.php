<?php

namespace Natue\Bundle\InvoiceBundle\Services;

use Natue\Bundle\InvoiceBundle\Entity\Invoice as InvoiceEntity;
use Natue\Bundle\InvoiceBundle\Taxman;

/**
 * Class TaxmanService
 * @package Natue\Bundle\InvoiceBundle\Services
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class TaxmanService
{
    /**
     * @var \Natue\Bundle\InvoiceBundle\Taxman\InvoiceFactory
     */
    protected $invoiceFactory;

    /**
     * @var \Natue\Bundle\InvoiceBundle\Taxman\Client
     */
    protected $taxman;

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxman\InvoiceFactory $invoiceFactory
     * @param \Natue\Bundle\InvoiceBundle\Taxman\Client         $taxman
     */
    public function __construct(Taxman\InvoiceFactory $invoiceFactory, Taxman\Client $taxman)
    {
        $this->invoiceFactory = $invoiceFactory;
        $this->taxman         = $taxman;
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Entity\Invoice $invoiceEntity
     * @return \Natue\Bundle\InvoiceBundle\Taxman\Invoice
     */
    public function issue(InvoiceEntity $invoiceEntity)
    {
        $invoice = $this->invoiceFactory
            ->create($invoiceEntity);

        $this->taxman
            ->invoice()
            ->create($invoice);

        return $invoice;
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Entity\Invoice $invoiceEntity
     * @return \Natue\Bundle\InvoiceBundle\Taxman\Outcome
     */
    public function outcome(InvoiceEntity $invoiceEntity)
    {
        $invoiceData = $this->taxman
            ->invoice()
            ->fetch($invoiceEntity->getId());

        if (array_key_exists('error', $invoiceData)) {
            return $this->getDefaultOutcomeForEmptyInvoiceData($invoiceData['error']);
        }

        $outcome = (new Taxman\Outcome())
            ->withSuccess(false)
            ->withReason($invoiceData['reason'])
            ->withNfeKey($invoiceData['invoice_key'])
            ->withNfeXml($invoiceData['xml']);

        if ($this->hasBeenCreated($invoiceData)) {
            return $outcome->withSuccess(true)
                ->withStatus(Taxman\Outcome::CREATED);
        }

        if ($this->shouldBeDisposed($invoiceData)) {
            return $outcome->withStatus(Taxman\Outcome::DISPOSABLE);
        }

        return $outcome->withStatus(Taxman\Outcome::REJECTED);
    }

    /**
     * @param string $reason
     * @return \Natue\Bundle\InvoiceBundle\Taxman\Outcome
     */
    protected function getDefaultOutcomeForEmptyInvoiceData($reason)
    {
        return (new Taxman\Outcome())
            ->withSuccess(false)
            ->withStatus(Taxman\Outcome::REJECTED)
            ->withReason($reason);
    }

    /**
     * @param array $invoiceData
     * @return bool
     */
    protected function hasBeenCreated(array $invoiceData)
    {
        return (bool) ($invoiceData['status'] === 1);
    }

    /**
     * @param array $invoiceData
     * @return bool
     */
    protected function shouldBeDisposed(array $invoiceData)
    {
        if ($this->hasBeenCreated($invoiceData)) {
            return false;
        }

        return in_array(strtolower($invoiceData['reason']), ['duplicated', 'disposed']);
    }
}
