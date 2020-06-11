<?php

namespace Natue\Bundle\ZedBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\Common\Collections\ArrayCollection;

use Natue\Bundle\StockBundle\Service\StockItemManager;
use Natue\Bundle\ZedBundle\Service\HttpClient;

class FailedItemsAssignmentCommand extends ItemsAssignmentCommand
{
    /**
     * Setup command usage
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('natue:zed:failed-items-assignment');
        $this->setDescription('Assign "clarify_picking_failed" zed_items with stock_items');
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

            $itemsAssigned = $this->assignItems($stockItemManager, $input->getOption('limit'));

            if (!$itemsAssigned->isEmpty()) {
                $stockItemManager->log("There is {$itemsAssigned->count()} now assigned.");

                $successfulRequests = $this->notifyIsPicking($itemsAssigned);

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
        return $stockItemManager->assignForFailedPickingOrderItemsWithStockItems(
            $limit
        );
    }

    /**
     * @param ArrayCollection $itemsAssigned
     * @return int
     */
    protected function notifyIsPicking(ArrayCollection $itemsAssigned)
    {
        /** @var HttpClient $zedHttpClient */
        $zedHttpClient = $this->getContainer()->get('natue.zed.http_client');
        $successfulRequests = 0;

        foreach ($itemsAssigned as $salesOrderItemId) {
            $successfulRequests += $zedHttpClient->isPickingForOrderItemId($salesOrderItemId->getId());
        }

        return $successfulRequests;
    }
}
