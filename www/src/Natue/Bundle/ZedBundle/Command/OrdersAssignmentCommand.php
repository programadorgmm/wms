<?php

namespace Natue\Bundle\ZedBundle\Command;

use Natue\Bundle\ZedBundle\Entity\ZedOrderItem;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\Common\Collections\ArrayCollection;

use Natue\Bundle\StockBundle\Service\StockItemManager;

/**
 * Class OrdersAssignmentCommand
 * @package Natue\Bundle\ZedBundle\Command
 */
class OrdersAssignmentCommand extends ContainerAwareCommand
{
    /**
     * Setup command usage
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('natue:zed:orders-assignment');
        $this->setDescription('Assign zed_orders with stock_items');
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
            'Set a limit for assignment'
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

        try {
            /**
             * @var ArrayCollection $itemsPickedFailed
             * @var ArrayCollection $itemsBackToReadyForPicking
             */
            $stockItemManager->log("Searching for refunded items");
            $stockItemManager->clearAssignmentForRefundedItems();
            $stockItemManager->log("Cleared assignments for refunded items");

            $response =  $this->assignOrders($stockItemManager, $input->getOption('limit'));
            $this->handleItemsPickedFailed($stockItemManager, $output, $response['itemsPickedFailed']);
            $this->handleItemsBackToReadyForPicking($stockItemManager, $output, $response['itemsBackToReadyForPicking']);

            $wmsConnection->commit();
        } catch (\Exception $e) {
            $wmsConnection->rollback();
            $wmsConnection->close();
            $output->writeln('Assignment failure.');
            throw $e;
        }

        $output->writeln('Assignment success.');
    }

    /**
     * @param StockItemManager $stockItemManager
     * @param OutputInterface $output
     * @param ArrayCollection $itemsPickedFailed
     */
    protected function handleItemsPickedFailed(
        StockItemManager $stockItemManager,
        OutputInterface $output,
        ArrayCollection $itemsPickedFailed
    ) {
        if ($itemsPickedFailed->isEmpty()) {
            return ;
        }

        $ids = implode(',', $itemsPickedFailed->map(function (ZedOrderItem $zedOrderItem) {
            return $zedOrderItem->getId();
        })->getValues());

        $stockItemManager->log(
            "There is {$itemsPickedFailed->count()} items with some issue. zed_order_items: {$ids}"
        );

        $successfulRequests = $this->notifyPickedFailsToZed($itemsPickedFailed);

        $output->writeln(
            sprintf(
                'Successful requests to ZED %d',
                $successfulRequests
            )
        );
    }

    /**
     * @param StockItemManager $stockItemManager
     * @param OutputInterface $output
     * @param ArrayCollection $itemsBackToReadyForPicking
     */
    protected function handleItemsBackToReadyForPicking(
        StockItemManager $stockItemManager,
        OutputInterface $output,
        ArrayCollection $itemsBackToReadyForPicking
    ) {
        if ($itemsBackToReadyForPicking->isEmpty()) {
            return ;
        }

        $ids = implode(',', $itemsBackToReadyForPicking->map(function (ZedOrderItem $zedOrderItem) {
            return $zedOrderItem->getId();
        })->getValues());

        $stockItemManager->log("There is {$itemsBackToReadyForPicking->count()} now assigned. zed_order_items: {$ids}");

        $successfulRequests = $this->notifyIsPicking($itemsBackToReadyForPicking);

        $output->writeln(
            sprintf(
                'Successful requests to ZED %d',
                $successfulRequests
            )
        );
    }

    /**
     * @param StockItemManager $stockItemManager
     * @param $limit
     * @return array
     */
    protected function assignOrders(StockItemManager $stockItemManager, $limit)
    {
        $redis = $this->getContainer()->get('snc_redis.default');

        return $stockItemManager->assignForOrdersWithStockItems(
            $limit,
            $redis
        );
    }

    /**
     * @param ArrayCollection $itemsPickedFailed
     * @return int
     */
    protected function notifyPickedFailsToZed(ArrayCollection $itemsPickedFailed)
    {
        /**
         * @var ZedOrderItem $salesOrderItem
         */
        $redis = $this->getContainer()->get('snc_redis.default');

        foreach ($itemsPickedFailed as $salesOrderItem) {
            $redis->rpush(NotifyZedCommand::ITEMS_FAILED_KEY, $salesOrderItem->getId());
        }

        return count($itemsPickedFailed);
    }

    /**
     * @param ArrayCollection $itemsAssigned
     * @return int
     */
    protected function notifyIsPicking(ArrayCollection $itemsAssigned)
    {
        /**
         * @var ZedOrderItem $salesOrderItem
         */
        $redis = $this->getContainer()->get('snc_redis.default');


        foreach ($itemsAssigned as $salesOrderItem) {
            $redis->rpush(NotifyZedCommand::ITEMS_BACK_TO_READY_KEY, $salesOrderItem->getId());
        }

        return count($itemsAssigned);
    }
}
