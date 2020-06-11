<?php

namespace Natue\Bundle\InvoiceBundle\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Natue\Bundle\InvoiceBundle\Services\InvoiceService;
use Natue\Bundle\StockBundle\Repository\StockItemRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InvoiceInventoryLossCommand
 * @package Natue\Bundle\InvoiceBundle\Command
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class InvoiceInventoryLossCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Natue\Bundle\InvoiceBundle\Services\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var array
     */
    protected $success = [];

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('natue:invoice:inventory-loss');
        $this->setDescription('Issue inventory loss invoices');
    }

    /**
     * Execute command
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        date_default_timezone_set('UTC');

        /** @var EntityManager entityManager */
        $this->entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        /** @var InvoiceService invoiceService */
        $this->invoiceService = $this->getContainer()->get('natue.invoice.service');

        /** @var StockItemRepository $stockItemRepository */
        $stockItemRepository = $this->getContainer()->get('natue.stock.item.repository');

        /** @var array|\Doctrine\Common\Collections\ArrayCollection[]|\Natue\Bundle\StockBundle\Entity\StockItem[][] $groups */
        $groups = $stockItemRepository->findInvoiceableItemsGroupedByPurchaseOrder();

        foreach ($groups as $key => $stockItems) {
            $this->issueInvoice($stockItems);
        }

        $this->logResult($output);
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|\Natue\Bundle\StockBundle\Entity\StockItem[] $stockItems
     */
    protected function issueInvoice(ArrayCollection $stockItems)
    {
        $transaction = function () use ($stockItems) {
            $purchaseOrder = $stockItems->first()
                ->getPurchaseOrderItem()
                ->getPurchaseOrder();

            $invoice = $this->invoiceService->create($purchaseOrder, $stockItems);
            $this->invoiceService->initialize($invoice);

            $this->success[$invoice->getPurchaseOrder()->getId()] = $stockItems->count();
        };

        try {
            $this->transaction($transaction);
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function logResult(OutputInterface $output)
    {
        $errorsCount   = count($this->errors);
        $invoicesCount = count($this->success);
        $itemsCount    = array_sum(array_values($this->success));

        $completedMessage = $errorsCount > 0
            ? sprintf('Completed with %s errors!', $errorsCount)
            : 'Completed!';

        $this->log($output, $completedMessage);

        foreach ($this->errors as $error) {
            $this->log($output, $error, 'ERROR');
        }

        if (!$this->success) {
            return;
        }

        $this->log($output, sprintf('Created %s invoices (%s items)', $invoicesCount, $itemsCount));
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $message
     * @param string                                            $level
     * @return void
     */
    protected function log(OutputInterface $output, $message, $level = 'INFO')
    {
        $output->writeln(sprintf('[%s] -> %s', $level, $message));
    }

    /**
     * @param \Closure $transaction
     * @return mixed
     * @throws \Exception
     */
    protected function transaction(\Closure $transaction)
    {
        $this->entityManager->beginTransaction();

        try {
            $result = call_user_func($transaction, $this->entityManager);
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            throw $e;
        }

        $this->entityManager->commit();

        return $result;
    }
}
