<?php

namespace Natue\Bundle\ZedBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\Common\Collections\ArrayCollection;

use Natue\Bundle\StockBundle\Service\StockItemManager;
use Natue\Bundle\ZedBundle\Service\HttpClient;

class ItemsAssignmentCommand extends ContainerAwareCommand
{
    /**
     * Setup command usage
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('natue:zed:items-assignment');
        $this->setDescription('Assign "ready for picking" zed_items with stock_items');
        $this->addOption(
            'debug',
            null,
            InputOption::VALUE_NONE,
            'If set, the task will run showing messages'
        );

        $this->addOption(
            'limit',
            null,
            InputOption::VALUE_REQUIRED,
            'Set a limit for assingment'
        );
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var StockItemManager $stockItemManager */
        $stockItemManager = $this->getContainer()->get('natue.stock.item.manager');

        if ($input->getOption('debug')) {
            $stockItemManager->setDebugOn();
        }

        $wmsConnection = $this->getContainer()->get('doctrine.dbal.default_connection');
        $wmsConnection->beginTransaction();

        $stockItemManager->log("Begin transaction");

        try {
            $stockItemManager->log("Searching for refunded items");
            $stockItemManager->clearAssignmentForRefundedItems();
            $stockItemManager->log("Cleared assignments for refunded items");

            $itemsPickedFailed = $this->assignItems($stockItemManager, $input->getOption('limit'));

            if (!$itemsPickedFailed->isEmpty()) {
                $stockItemManager->log("There is {$itemsPickedFailed->count()} items with some issue.");

                $successfulRequests = $this->notifyPickedFailsToZed($itemsPickedFailed);

                $output->writeln(
                    sprintf(
                        'Successful requests to ZED %d',
                        $successfulRequests
                    )
                );
            }

            $wmsConnection->commit();
            $stockItemManager->log("Transaction Commited");
        } catch (\Exception $e) {
            $stockItemManager->log("Rolling back transaction", "ERROR");
            $wmsConnection->rollback();
            $wmsConnection->close();

            $output->writeln('Assignment failure.');
            throw $e;
        }

        $output->writeln('Assignment success.');
    }

    protected function assignItems(StockItemManager $stockItemManager, $limit)
    {
        return $stockItemManager->assignForPickingOrderItemsWithStockItems(
            $limit
        );
    }

    /**
     * @param ArrayCollection $itemPickedFailed
     * @return int
     */
    protected function notifyPickedFailsToZed(ArrayCollection $itemsPickedFailed)
    {
        /** @var HttpClient $zedHttpClient */
        $zedHttpClient = $this->getContainer()->get('natue.zed.http_client');
        $successfulRequests = 0;

        foreach ($itemsPickedFailed as $salesOrderItemId) {
            $successfulRequests += $zedHttpClient->clarifyPickingFailedForOrderItemId($salesOrderItemId->getId());
        }

        return $successfulRequests;
    }
}
