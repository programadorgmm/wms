<?php

namespace Natue\Bundle\StockBundle\Service;

use Doctrine\ORM\NoResultException;
use Finite\Exception\StateException;
use Natue\Bundle\CoreBundle\AppEvent;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Predis\Client as RedisClient;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\StockBundle\Entity\StockItem;
use Natue\Bundle\StockBundle\Entity\StockPosition;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Repository\StockItemRepository;
use Natue\Bundle\StockBundle\StateMachine\StockItem as StockItemStateMachine;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType as StockItemStateTransition;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Natue\Bundle\ZedBundle\Entity\ZedOrderItem;
use Natue\Bundle\ZedBundle\Repository\ZedOrderItemRepository;
use Natue\Bundle\ZedBundle\Repository\ZedOrderRepository;

class StockItemManager
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var ZedOrderItemRepository
     */
    protected $zedOrderItemRepository;

    /**
     * @var ZedOrderRepository
     */
    protected $zedOrderRepository;

    /**
     * @var StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * @var StockItemStateMachine
     */
    protected $stockItemStateMachine;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var string
     */
    protected $sellItemsWithExpirationGreaterThan;

    protected $availableToAssign = [];

    protected $stockItems = [];

    /**
     * @param Registry              $doctrine
     * @param SecurityContext       $securityContext
     * @param StockItemStateMachine $stockItemStateMachine
     * @param string                $sellItemsWithExpirationGreaterThan
     */
    public function __construct(
        Registry $doctrine,
        SecurityContext $securityContext,
        StockItemStateMachine $stockItemStateMachine,
        $sellItemsWithExpirationGreaterThan
    ) {
        $this->doctrine      = $doctrine;
        $this->entityManager = $doctrine->getManager();

        $this->stockItemRepository    = $doctrine->getRepository('NatueStockBundle:StockItem');
        $this->zedOrderItemRepository = $doctrine->getRepository('NatueZedBundle:ZedOrderItem');
        $this->zedOrderRepository = $doctrine->getRepository('NatueZedBundle:ZedOrder');

        // cron doesn't have securityToken defined
        $securityToken = $securityContext->getToken();
        $this->user = ($securityToken) ? $securityToken->getUser() : null;

        $this->stockItemStateMachine = $stockItemStateMachine;
        $this->sellItemsWithExpirationGreaterThan = $sellItemsWithExpirationGreaterThan;
    }

    const ORDER_EXPIRES = 2;
    const ORDER_LOCK_KEY = 'order_lock_%s';

    /**
     * Define variable debug as true
     *
     * @return void
     */
    public function setDebugOn()
    {
        $this->debug = true;
    }

    /**
     * @param $message
     * @param string $level
     */
    public function log($message, $level = "INFO")
    {
        if ($this->debug) {
            printf("[$level] -> $message \n");
        }
    }

    /**
     * Move StockItem into StockPosition
     *
     * @param StockItem     $stockItem
     * @param StockPosition $stockPosition
     * @param boolean       $validate
     *
     * @return void
     */
    public function changePosition(StockItem $stockItem, StockPosition $stockPosition, $validate = true)
    {
        if ($validate) {
            $this->validatePosition($stockItem, $stockPosition);
        }

        $stockItem->setStockPosition($stockPosition);

        $this->entityManager->persist($stockItem);
        $this->entityManager->flush();
    }

    /**
     * @param StockItem $stockItem
     * @param string    $transition
     */
    public function changeStatus(StockItem $stockItem, $transition)
    {
        $this->stockItemStateMachine->applyTransitionOnEntity($transition, $stockItem);
        $this->entityManager->persist($stockItem);
        $this->entityManager->flush();
    }

    /**
     * @param ArrayCollection $stockItems
     */
    private function stockItemSaveCollection(ArrayCollection $stockItems)
    {
        foreach ($stockItems as $item) {
            $this->entityManager->persist($item);
            $this->entityManager->flush();
        }
    }

    /**
     * @param ZedOrder $zedOrder
     *
     * @throws \Finite\Exception\StateException
     */
    public function markZedOrderAsWaitingForPicking(ZedOrder $zedOrder)
    {
        $stockItems = $this->stockItemRepository->getAssignedItemsForZedOrder($zedOrder);
        $this->stockItemStateMachine->applyTransitionOnCollection(
            EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
            $stockItems
        );
        $this->stockItemSaveCollection($stockItems);
    }

    /**
     * @param ZedOrder $zedOrder
     * @param EventDispatcherInterface|null $eventDispatcher
     *
     * @throws StateException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function markZedOrderAsPicked(ZedOrder $zedOrder, EventDispatcherInterface $eventDispatcher = null)
    {
        $stockItems = $this->stockItemRepository->getWaitingForPickingItemsForZedOrder($zedOrder);

        $this->stockItemStateMachine->applyTransitionOnCollection(
            EnumStockItemStatusType::STATUS_PICKED,
            $stockItems
        );

        if ($eventDispatcher) {
            $this->dispatchStockItemUpdatedEventToItems($eventDispatcher, $stockItems);
        }

        $this->removeItemsFromTheirPositions($stockItems);
    }

    /**
     * @param $zedOrder
     * @throws StateException
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function markStockItemAsSold($zedOrder)
    {
        $stockItems = $this->stockItemRepository->getReadyForShippingItemsForZedOrder($zedOrder);
        $this->stockItemStateMachine->applyTransitionOnCollection(
            EnumStockItemStatusType::STATUS_SOLD,
            $stockItems
        );
        $this->stockItemSaveCollection($stockItems);
    }

    /**
     * @param ZedOrder $zedOrder
     * @throws StateException
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function markZedOrderAsReadyForShipping(ZedOrder $zedOrder)
    {
        $stockItems = $this->stockItemRepository->getPickedItemsForZedOrder($zedOrder);
        $this->stockItemStateMachine->applyTransitionOnCollection(
            EnumStockItemStatusType::STATUS_READY_FOR_SHIPPING,
            $stockItems
        );
        $this->stockItemSaveCollection($stockItems);
    }

    /**
     * @param int $limit
     * @return ArrayCollection
     */
    public function assignForPickingOrderItemsWithStockItems($limit)
    {
        return $this->assignForItemsWithStockItems(
            $this->findReadyForPickingItemsWithoutStockAssignment($limit)
        );
    }

    /**
     * @param int $limit
     * @param RedisClient $redis
     *
     * @return array
     */
    public function assignForOrdersWithStockItems($limit, RedisClient $redis)
    {
        $lockedOrders = array_map(function ($key) {
            return substr($key, strlen(self::ORDER_LOCK_KEY) - 2);
        }, $redis->keys(
            sprintf(self::ORDER_LOCK_KEY, '*')
        ));

        $zedOrders =  $this->findOrdersWithoutStockAssignment($limit, $lockedOrders);
        $itemsPickedFailed = new ArrayCollection();
        $itemsBackToReadyForPicking = new ArrayCollection();

        $this->log("Searching for orders without assignment");
        $this->log("Found {$zedOrders->count()} orders without assignment");

        /**
         * @var ZedOrder $zedOrder
         */
        foreach ($zedOrders as $zedOrder) {
            try {
                $map = $this->availableToCheck($zedOrder);

                $stockItemBag = new ArrayCollection();
                $orderFailedItemsBag = new ArrayCollection();

                $this->log("Search for assignment to order {$zedOrder->getId()}");

                /**
                 * @var ZedOrderItem $zedOrderItem
                 */
                foreach ($zedOrder->getZedOrderItems() as $zedOrderItem) {
                    $status = $zedOrderItem->getZedOrderItemStatus()->getName();
                    $this->log("checking order item {$zedOrderItem->getId()} for status {$status}");

                    if (in_array($status, [
                        'refunded',
                        'refund_needed',
                        'refunded_with_credit',
                        'cancel_refunded_credit',
                    ])) {
                        continue;
                    }

                    if (!in_array($status, ['ready_for_picking', 'clarify_picking_failed'])) {
                        throw new \DomainException(
                            sprintf('Unexpected status %s on zed order %s', $status, $zedOrder->getId())
                        );
                    }

                    try {
                        $stockItem = $this->getAvailableStockItem($zedOrderItem->getZedProduct(), $map[$zedOrderItem->getZedProduct()->getSku()]['quantity']);
                    } catch (\Exception $exception) {
                        $orderFailedItemsBag->add($zedOrderItem);
                        continue;
                    }

                    $stockItemBag->add([
                        'stockItem' => $stockItem,
                        'zedOrderItem' => $zedOrderItem
                    ]);

                    if ($status === 'clarify_picking_failed') {
                        $itemsBackToReadyForPicking->add($zedOrderItem);
                    }
                }

                if (!$orderFailedItemsBag->isEmpty()) {
                    foreach ($orderFailedItemsBag as $item) {
                        $itemsPickedFailed->add($item);
                    }

                    $this->log("Assigned fail to order {$zedOrder->getId()} ");

                    $redis->setex(
                        sprintf(self::ORDER_LOCK_KEY, $zedOrder->getId()),
                        self::ORDER_EXPIRES * 60 * 60,
                        1
                    );

                    continue;
                }

                $this->assignOrder($stockItemBag);
            } catch (\Exception $e) {
                $ordersFailed[$zedOrder->getId()] = $zedOrder->getId();
                $this->log("Assignment global fail to order {$zedOrder->getId()}");

                $redis->setex(
                    sprintf(self::ORDER_LOCK_KEY, $zedOrder->getId()),
                    self::ORDER_EXPIRES * 60 * 60,
                    1
                );

                // new relic
                $this->log($e->getMessage());
            }
        }

        return [
            'itemsPickedFailed' => $itemsPickedFailed,
            'itemsBackToReadyForPicking' => $itemsBackToReadyForPicking
        ];
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param \Traversable $stockItems
     */
    protected function dispatchStockItemUpdatedEventToItems(
        EventDispatcherInterface $dispatcher,
        \Traversable $stockItems
    ) {
        $eventData = [];

        /**
         * @var StockItem $item
         */
        foreach ($stockItems as $item) {
            $eventData[] = [
                'zed_product' => $item->getZedProduct()->getId(),
                'barcode'     => $item->getBarcode()
            ];
        }

        $dispatcher->dispatch(
            'stock_item.updated',
            new AppEvent($eventData)
        );
    }

    protected function removeItemsFromTheirPositions(ArrayCollection $stockItems)
    {
        /**
         * @var StockItem $item
         */
        foreach ($stockItems as $item) {
            $item->setStockPosition(null);
        }

        $this->stockItemSaveCollection($stockItems);
    }

    protected function assignOrder(ArrayCollection $items)
    {
        /**
         * @var ZedOrderItem $zedOrderItem
         * @var StockItem $stockItem
         */
        try {
            foreach ($items as $item) {

                $stockItem = $item['stockItem'];
                $zedOrderItem = $item['zedOrderItem'];
                $this->log("assign order item  {$zedOrderItem->getId()}");
                $this->decrementAvailableStockForItem($stockItem);
                $this->assignStockItemWithZedOrderItem($stockItem, $zedOrderItem);
                $this->entityManager->persist($stockItem);
                $this->entityManager->flush();
            }

            $this->log("Order {$zedOrderItem->getZedOrder()->getId()} Assigned");
        } catch (\Exception $stateException) {
            throw $stateException;
        }
    }


    protected function assignForItemsWithStockItems(ArrayCollection $zedOrderItems)
    {
        $itemsPickedFailed = new ArrayCollection();
        $this->log("Searching for ready_for_picking without assignment");

        $this->log("Found {$zedOrderItems->count()} ready_for_picking without assignment");

        /** @var ZedOrderItem $zedOrderItem */
        foreach ($zedOrderItems as $zedOrderItem) {
            try {
                $this->log("Searching for stock item to {$zedOrderItem->getId()}");

                $stockItem = $this->stockItemRepository
                    ->findOneByZedProductWithEarliestDateExpiration($zedOrderItem->getZedProduct());

                $this->log("Search for assignment to {$zedOrderItem->getId()}");

                if ($this->stockItemRepository->findOneByZedOrderItem($zedOrderItem->getId())) {
                    $this->log("ZedOrderItem already assigned, changing status");
                    $this->changeStatus($stockItem, StockItemStateTransition::STATUS_ASSIGNED);
                    $this->log("Status changed");

                    continue;
                }

                $this->log("Assigning stockItem: {$stockItem->getId()} to zedOrderItem: {$zedOrderItem->getId()} ");

                try {
                    $this->assignStockItemWithZedOrderItem($stockItem, $zedOrderItem);
                    $this->entityManager->persist($stockItem);
                    $this->log("Assigned");
                } catch (StateException $stateException) {
                    $this->log("Cannot assign stockItem: {$stockItem->getId()} to zedOrderItem: {$zedOrderItem->getId()} Error: {$stateException->getMessage()}");
                }
            } catch (\Exception $e) {
                $this->log("Assignment fail to {$zedOrderItem->getId()}");
                $itemsPickedFailed->add($zedOrderItem);
            }

            $this->entityManager->flush();
        }

        return $itemsPickedFailed;
    }

    public function assignForFailedPickingOrderItemsWithStockItems($limit)
    {
        return $this->assignForFailedItemsWithStockItems(
            $this->findClarifyPickingFailedItemsWithoutStockAssignment($limit)
        );
    }

    protected function assignForFailedItemsWithStockItems(ArrayCollection $zedOrderItems)
    {
        $itemsAssigned = new ArrayCollection();
        $this->log("Searching for clarify_picking_failed without assignment");

        $this->log("Found {$zedOrderItems->count()} clarify_picking_failed without assignment");

        /** @var ZedOrderItem $zedOrderItem */
        foreach ($zedOrderItems as $zedOrderItem) {
            try {
                $this->log("Searching for stock item to {$zedOrderItem->getId()}");

                $stockItem = $this->stockItemRepository
                    ->findOneByZedProductWithEarliestDateExpiration($zedOrderItem->getZedProduct());

                $this->log("Search for assignment to {$zedOrderItem->getId()}");

                if ($this->stockItemRepository->findOneByZedOrderItem($zedOrderItem->getId())) {
                    $this->log("ZedOrderItem already assigned, changing status");
                    $this->changeStatus($stockItem, StockItemStateTransition::STATUS_ASSIGNED);
                    $this->log("Status changed");

                    continue;
                }

                $this->log("Assigning stockItem: {$stockItem->getId()} to zedOrderItem: {$zedOrderItem->getId()} ");

                try {
                    $this->assignStockItemWithZedOrderItem($stockItem, $zedOrderItem);
                    $this->entityManager->persist($stockItem);
                    $itemsAssigned->add($zedOrderItem);
                    $this->log("Assigned");
                } catch (StateException $stateException) {
                    $this->log("Cannot assign stockItem: {$stockItem->getId()} to zedOrderItem: {$zedOrderItem->getId()} Error: {$stateException->getMessage()}");
                }
            } catch (\Exception $e) {
                $this->log("Assignment fail to {$zedOrderItem->getId()}");
            }

            $this->entityManager->flush();
        }

        return $itemsAssigned;
    }

    protected function findOrdersWithoutStockAssignment($limit, $lockedOrders = [])
    {
        return new ArrayCollection(
            $this->zedOrderRepository->findOrdersWithoutStockAssignment($limit, [], $lockedOrders)
        );
    }

    protected function findReadyForPickingItemsWithoutStockAssignment($limit)
    {
        return new ArrayCollection(
            $this->zedOrderItemRepository->findReadyForPickingItemsWithoutStockAssignment($limit)
        );
    }

    protected function findClarifyPickingFailedItemsWithoutStockAssignment($limit)
    {
        return new ArrayCollection(
            $this->zedOrderItemRepository->findClarifyPickingFailedItemsWithoutStockAssignment($limit)
        );
    }

    /**
     * @throws \Finite\Exception\StateException
     * @return void
     */
    public function clearAssignmentForRefundedItems()
    {
        $stockItems = new ArrayCollection(
            $this->stockItemRepository->findItemsAssignedToRefundedZedItems()
        );

        $this->log("Found ".$stockItems->count()." items to be refunded");
        $this->movePickingItemsToWaitingForStoragePosition($stockItems);

        $this->stockItemStateMachine->applyTransitionOnCollection(
            StockItemStateTransition::STATUS_READY,
            $stockItems
        );

        $this->unassignStockItemsFromZedItems($stockItems->toArray());
        $this->log("Unassigned ".$stockItems->count()." items.");
    }

    /**
     * Move to the special position those StockItem-s, that where in
     * the picking process already. (either "waiting_for_picking" or "picked")
     *
     * @param ArrayCollection $stockItems
     *
     * @return void
     */
    private function movePickingItemsToWaitingForStoragePosition(ArrayCollection $stockItems)
    {
        $validStatuses = [
            EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
            EnumStockItemStatusType::STATUS_PICKED
        ];

        $stockPositionRepository = $this->doctrine->getRepository('NatueStockBundle:StockPosition');
        $specialPosition = $stockPositionRepository->find(StockPosition::WAITING_FOR_STORAGE_POSITION_ID);

        /** @var StockItem $stockItem */
        foreach ($stockItems as $stockItem) {
            if (in_array($stockItem->getStatus(), $validStatuses)) {
                $this->changePosition($stockItem, $specialPosition, false);
            }
        }
    }

    /**
     * @param $stockItemId
     *
     * @return StockItem
     */
    public function findStockItem($stockItemId)
    {
        return $this->stockItemRepository->find($stockItemId);
    }

    /**
     * @param StockItem     $stockItem
     * @param StockPosition $stockPosition
     * @param string        $statusTransition
     * @throws \Exception
     * @return void
     */
    public function tryReturningProductToPositionAndUpdateStatus(
        StockItem $stockItem,
        StockPosition $stockPosition,
        $statusTransition
    ) {
        $this->changePosition($stockItem, $stockPosition);
        $this->changeStatus($stockItem, StockItemStateTransition::STATUS_RETURNED);
        $this->changeStatus($stockItem, $statusTransition);
    }

    /**
     * @param int $orderId
     * @return void
     */
    public function tryOrderReturnConfirmation($orderId)
    {
        $stockItems = $this->stockItemRepository->findOrderStockItems($orderId);
        $this->validateOrderItemsStatuses($orderId, $stockItems);
        $this->unassignStockItemsFromZedItems($stockItems);
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @return void
     */
    public function updateItemsStatusToReady(PurchaseOrder $purchaseOrder)
    {
        $stockItems = new ArrayCollection(
            $this->stockItemRepository->findIncomingItemsByPurchaseOrderAndUser(
                $purchaseOrder,
                $this->user
            )
        );

        $this->stockItemStateMachine->applyTransitionOnCollection(
            StockItemStateTransition::STATUS_READY,
            $stockItems
        );
    }

    /**
     * @param ZedOrder $zedOrder
     * @return void
     */
    public function confirmSoldForOrder(ZedOrder $zedOrder)
    {
        $stockItems = new ArrayCollection(
            $this->stockItemRepository->getOrderVolumesItems($zedOrder)
        );

        $this->stockItemStateMachine->applyTransitionOnCollection(
            StockItemStateTransition::STATUS_SOLD,
            $stockItems
        );
    }

    /**
     * @param string $sellItemsWithExpirationGreaterThan
     *
     * @return string
     */
    public function getCurrentSellableStockData($sellItemsWithExpirationGreaterThan = null)
    {
        if (is_null($sellItemsWithExpirationGreaterThan)) {
            $sellItemsWithExpirationGreaterThan = $this->sellItemsWithExpirationGreaterThan;
        }

        return json_encode($this->stockItemRepository->getCurrentSellableStockData($sellItemsWithExpirationGreaterThan));
    }

    /**
     * @param ZedProduct $zedProduct
     * @param string $sellItemsWithExpirationGreaterThan
     *
     * @return int|null
     */
    public function getCurrentSellableStockDataByZedProduct(ZedProduct $zedProduct, $sellItemsWithExpirationGreaterThan = null)
    {
        if (is_null($sellItemsWithExpirationGreaterThan)) {
            $sellItemsWithExpirationGreaterThan = $this->sellItemsWithExpirationGreaterThan;
        }

        return $this->stockItemRepository->getCurrentSellableStockDataByZedProduct($zedProduct, $sellItemsWithExpirationGreaterThan);
    }

    /**
     * @param StockItem     $stockItem
     * @param StockPosition $stockPosition
     * @throws \Exception
     * @return void
     */
    protected function validatePosition(StockItem $stockItem, StockPosition $stockPosition)
    {
        $result = $this->stockItemRepository->findFirstByZedProductAndPositionWithDifferentDateExpirationAndBarcode(
            $stockItem->getZedProduct(),
            $stockPosition,
            $stockItem->getDateExpiration(),
            $stockItem->getBarcode()
        );

        if ($result) {
            throw new \Exception('Position already have the same product with different expiration date or barcode');
        }
    }

    /**
     * @param StockItem $stockItem
     * @param ZedOrderItem $zedOrderItem
     * @return void
     */
    private function assignStockItemWithZedOrderItem(StockItem $stockItem, ZedOrderItem $zedOrderItem)
    {
        $this->changeStatus($stockItem, StockItemStateTransition::STATUS_ASSIGNED);
        $stockItem->setZedOrderItem($zedOrderItem);
    }

    /**
     * @param int   $orderId
     * @param array $stockItems
     * @throws \Exception
     * @return void
     */
    private function validateOrderItemsStatuses($orderId, array $stockItems)
    {
        $validStatusList = [
            EnumStockItemStatusType::STATUS_DAMAGED,
            EnumStockItemStatusType::STATUS_READY,
            EnumStockItemStatusType::STATUS_EXPIRED
        ];

        $validItemsAmount = $this->stockItemRepository->countOrderItemsWithStatus($orderId, $validStatusList);

        if ($validItemsAmount != count($stockItems)) {
            throw new \Exception('Current OrderItem statuses are not valid');
        }
    }

    /**
     * @param array $stockItems
     * @return void
     */
    private function unassignStockItemsFromZedItems(array $stockItems)
    {
        /** @var StockItem $stockItem */
        foreach ($stockItems as $stockItem) {
            $stockItem->setZedOrderItem(null);

            $this->entityManager->persist($stockItem);
            $this->entityManager->flush();
        }
    }

    protected function availableToCheck(ZedOrder $zedOrder)
    {
        $map = [];
        foreach ($zedOrder->getZedOrderItems() as $zedOrderItem) {
            $sku = $zedOrderItem->getZedProduct()->getSku();
            if (!isset($map[$sku])) {
                $map[$sku] = [
                    'sku' => $sku,
                    'quantity' => 0
                ];
            }

            $map[$sku]['quantity'] += 1;
        }

        $alreadyAssigned = $this->stockItemRepository->findByZedOrderItem(
            $zedOrder->getZedOrderItems()->map(function (ZedOrderItem $item) {
                return $item->getId();
            })->toArray()
        );

        if ($alreadyAssigned) {
            throw new \DomainException(
                sprintf('Unexpected assigned item to zed order %s', $zedOrder->getId())
            );
        }

//        $this->checkQuantity($map);
        return $map;
    }

    protected function checkQuantity(array $map)
    {
        $qtd = count($this->availableToAssign);
        $this->log("quantidade {$qtd}");

        if ($intersect = array_intersect_key($map, $this->availableToAssign)) {
            foreach ($intersect as $sku => $item) {
                if ($this->availableToAssign[$sku]['quantity'] < $map[$sku]['quantity']) {
                    throw new \DomainException(sprintf('Zed Order Stock unavailable %s', $sku));
                }
            }
        }

        if ($diff = array_diff_key($this->availableToAssign, $map)) {
            $availableToAssign = $this->stockItemRepository->findAvailableToAssign(array_keys($diff));
            $keys = array_map(function (array $row) {
                return $row['sku'];
            }, $availableToAssign);

            $this->availableToAssign = array_merge(
                $this->availableToAssign,
                array_combine($keys, $availableToAssign)
            );
        }

        foreach ($diff as $sku => $item) {
            if ($this->availableToAssign[$sku]['quantity'] < $map[$sku]['quantity']) {
                throw new \DomainException(sprintf('Zed Order Stock unavailable %s', $sku));
            }
        }
    }

    protected function decrementAvailableStockForItem(StockItem $item)
    {
        if (!isset($this->availableToAssign[$item->getZedProduct()->getSku()])) {
            return ;
        }

        $this->availableToAssign[$item->getZedProduct()->getSku()]['quantity'] -= 1;
    }

    private function getAvailableStockItem(ZedProduct $zedProduct, $quantity)
    {
        if (!empty($this->stockItems[$zedProduct->getSku()])) {
            return array_shift($this->stockItems[$zedProduct->getSku()]);
        }

        $this->stockItems[$zedProduct->getSku()] = $this->stockItemRepository
            ->findByZedProductWithEarliestDateExpiration($zedProduct, [], $quantity);

        if (count($this->stockItems[$zedProduct->getSku()]) < $quantity) {
            throw new \DomainException("not found $quantity for stock item product {$zedProduct->getSku()}");
        }

        return array_shift($this->stockItems[$zedProduct->getSku()]);
    }
}
