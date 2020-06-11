<?php

namespace Natue\Bundle\StockBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\StockBundle\Entity\Inventory as InventoryEntity;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Entity\StockItem;
use Natue\Bundle\StockBundle\Entity\StockPosition;
use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\ZedBundle\Entity\EnumZedOrderItemStatusType;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;

/**
 * Repository for StockItem
 */
class StockItemRepository extends EntityRepository
{
    /**
     * @var array
     */
    private $sellableStatuses = [
        EnumStockItemStatusType::STATUS_READY,
        EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
        EnumStockItemStatusType::STATUS_ASSIGNED,
    ];

    /**
     * Define an array of default options for queries.
     *
     * @return array
     */
    private function getDefaultOptions()
    {
        return [
            'withLock' => true,
        ];
    }

    /**
     * @param ZedOrder $zedOrder
     * @return boolean
     */
    public function isReadyToPick(ZedOrder $zedOrder)
    {
        $query = $this->createQueryBuilder('stockItem')
            ->select('COUNT(stockItem.id)')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.id = stockItem.zedOrderItem'
            )
            ->where('zedOrderItem.zedOrder = :zedOrder')
            ->andWhere('stockItem.status != :status')
            ->setParameter('zedOrder', $zedOrder)
            ->setParameter('status', EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING)
            ->getQuery();

        return $query->getSingleScalarResult() == 0;
    }

    /**
     * @param array $stockItemIds
     * @param ZedOrder $zedOrder
     * @param array $options
     * @return ArrayCollection
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getByBarcodeAndZedOrder(array $stockItemIds, ZedOrder $zedOrder, array $options = [])
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('stockItem')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.id = stockItem.zedOrderItem'
            )
            ->where('zedOrderItem.zedOrder = :zedOrder')
            ->andWhere('stockItem.id IN (:stockItemIds)')
            ->setParameter('stockItemIds', $stockItemIds, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
            ->setParameter('zedOrder', $zedOrder)
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return new ArrayCollection($query->getResult());
    }

    /**
     * @param ZedOrder $order
     * @return int
     */
    public function getCountTotalItemsForZedOrder(ZedOrder $order)
    {
        $query = $this->createQueryBuilder('stockItem')
            ->select('COUNT(stockItem.id) as total')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.id = stockItem.zedOrderItem'
            )
            ->where('zedOrderItem.zedOrder = :zedOrderId')
            ->setParameter('zedOrderId', $order->getId())
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param array $filters
     *
     * @return QueryBuilder
     */
    public function getQueryForListAction(array $filters = [])
    {
        $queryBuilder = $this->createQueryBuilder('stockItem')
            ->addSelect('COUNT(zedProduct.sku) AS qty')
            ->addSelect('zedProduct.name AS productName')
            ->addSelect('zedProduct.sku AS productSku')
            ->addSelect('stockPosition.name AS stockPositionName')
            ->addSelect('stockItem.status AS statusName')
            ->addSelect('stockPosition.pickable AS pickable')
            ->innerJoin('stockItem.zedProduct', 'zedProduct')
            ->innerJoin('stockItem.stockPosition', 'stockPosition')
            ->addGroupBy('zedProduct.sku')
            ->addGroupBy('stockItem.stockPosition')
            ->addGroupBy('stockItem.barcode')
            ->addGroupBy('stockItem.dateExpiration')
            ->addGroupBy('stockItem.status')
            ->where('stockItem.status != :status')
            ->andWhere('stockPosition.enabled = :enabled')
            ->setParameters([
                'enabled' => true,
                'status'  => EnumStockItemStatusType::STATUS_SOLD,
            ]);

        return $queryBuilder;
    }

    /**
     * @param array $filters
     *
     * @return int
     */
    public function getNumberOfItems(array $filters = [])
    {
        $queryBuilder = $this->createQueryBuilder('stockItem')
            ->select('COUNT(stockItem.id) as numberOfItems');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array $filters
     * @param array $options
     *
     * @return array
     */
    public function findByFilters(
        array $filters = [],
        array $options = []
    )
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $queryBuilder = $this->createQueryBuilder('stockItem');

        if (!empty($filters['stockPosition'])) {
            $queryBuilder->innerJoin('stockItem.stockPosition', 'stockPosition')
                ->andWhere('stockItem.stockPosition = :stockPosition')
                ->setParameter('stockPosition', $filters['stockPosition']);
        }

        if (!empty($filters['barcode'])) {
            $queryBuilder->andWhere('stockItem.barcode = :barcode')
                ->setParameter('barcode', $filters['barcode']);
        }

        if (!empty($filters['status'])) {
            $queryBuilder->andWhere('stockItem.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['limit'])) {
            $queryBuilder->setMaxResults((int)$filters['limit']);
        }

        $query = $queryBuilder->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getResult();
    }

    /**
     * @param ZedProduct $zedProduct
     * @param StockPosition $stockPosition
     * @param string $status
     * @param \DateTime $dateExpiration
     * @param string $barcode
     * @param array $options
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findForUpdateAction(
        ZedProduct $zedProduct,
        StockPosition $stockPosition,
        $status,
        \DateTime $dateExpiration,
        $barcode,
        array $options = []
    )
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('stockItem')
            ->where('stockItem.zedProduct = :zedProduct')
            ->andWhere('stockItem.stockPosition = :stockPosition')
            ->andWhere('stockItem.status = :status')
            ->andWhere('stockItem.barcode = :barcode')
            ->andWhere('stockItem.dateExpiration = :dateExpiration')
            ->setParameters(
                [
                    'zedProduct'     => $zedProduct,
                    'stockPosition'  => $stockPosition,
                    'status'         => $status,
                    'barcode'        => $barcode,
                    'dateExpiration' => $dateExpiration->format('Y-m-d'),
                ]
            )
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getResult();
    }

    /**
     * Find one by ZedProduct with earliest DateExpiration
     *
     * @param ZedProduct $zedProduct
     * @param array $options
     * @param array $preReserved
     *
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function findOneByZedProductWithEarliestDateExpiration(
        ZedProduct $zedProduct,
        array $options = []
    )
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $queryBuilder = $this->createQueryBuilder('stockItem')
            ->innerJoin('stockItem.stockPosition', 'stockPosition')
            ->where('stockItem.zedProduct = :zedProduct')
            ->andWhere('stockPosition.pickable = :pickable')
            ->andWhere('stockPosition.enabled = :enabled')
            ->andWhere('stockItem.status = :status')
            ->andWhere('stockItem.zedOrderItem is null')
            ->orderBy('stockItem.dateExpiration')
            ->setMaxResults(1)
            ->setParameters(
                [
                    'zedProduct'  => $zedProduct,
                    'pickable'    => true,
                    'enabled'     => true,
                    'status'      => EnumStockItemStatusType::STATUS_READY
                ]
            );

        $query = $queryBuilder
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        $result = $query->getSingleResult();

        return $result;
    }

    /**
     * Find by ZedProduct with earliest DateExpiration
     *
     * @param ZedProduct $zedProduct
     * @param array $options
     * @param int $limit
     *
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function findByZedProductWithEarliestDateExpiration(
        ZedProduct $zedProduct,
        array $options = [],
        $limit = 10
    )
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $queryBuilder = $this->createQueryBuilder('stockItem')
            ->innerJoin('stockItem.stockPosition', 'stockPosition')
            ->where('stockItem.zedProduct = :zedProduct')
            ->andWhere('stockPosition.pickable = :pickable')
            ->andWhere('stockPosition.enabled = :enabled')
            ->andWhere('stockItem.status = :status')
            ->andWhere('stockItem.zedOrderItem is null')
            ->orderBy('stockItem.dateExpiration')
            ->setMaxResults($limit)
            ->setParameters(
                [
                    'zedProduct'  => $zedProduct,
                    'pickable'    => true,
                    'enabled'     => true,
                    'status'      => EnumStockItemStatusType::STATUS_READY
                ]
            );

        $query = $queryBuilder
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        $result = $query->getResult();

        return $result;
    }

    /**
     * Count Barcode and DateExpiration in Position
     *
     * @param               $barcode
     * @param \DateTime $dateExpiration
     * @param StockPosition $stockPosition
     *
     * @return int
     */
    public function countBarcodeAndDateExpirationInPosition(
        $barcode,
        \DateTime $dateExpiration,
        StockPosition $stockPosition
    )
    {
        $queryBuilder = $this->createQueryBuilder('stockItem')
            ->select('COUNT(stockItem.id) AS qty')
            ->where('stockItem.barcode = :barcode')
            ->andWhere('stockItem.stockPosition = :stockPosition')
            ->andWhere('stockItem.dateExpiration != :dateExpiration')
            ->setParameters(
                [
                    'barcode'        => $barcode,
                    'stockPosition'  => $stockPosition,
                    'dateExpiration' => $dateExpiration->format('Y-m-d'),
                ]
            );

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * Find "incoming" Stock Items by PurchaseOrder and User
     *
     * @param PurchaseOrder $purchaseOrder
     * @param User $user
     * @param array $options
     *
     * @return array
     */
    public function findIncomingItemsByPurchaseOrderAndUser(
        PurchaseOrder $purchaseOrder,
        User $user,
        array $options = []
    )
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('stockItem')
            ->innerJoin('stockItem.purchaseOrderItem', 'purchaseOrderItem')
            ->innerJoin('purchaseOrderItem.purchaseOrderItemReception', 'purchaseOrderItemReception')
            ->where('purchaseOrderItem.purchaseOrder = :purchaseOrder')
            ->andWhere('purchaseOrderItemReception.user = :user')
            ->andWhere('stockItem.status = :stockItemStatus')
            ->andWhere('purchaseOrderItem.status = :purchaseOrderItemStatus')
            ->setParameters(
                [
                    'purchaseOrder'           => $purchaseOrder,
                    'user'                    => $user,
                    'stockItemStatus'         => EnumStockItemStatusType::STATUS_INCOMING,
                    'purchaseOrderItemStatus' => EnumPurchaseOrderItemStatusType::STATUS_RECEIVING,
                ]
            )
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getResult();
    }

    /**
     * Find Items assigned to "refunded" ZedItems,
     * but were not "sold" yet (shipped).
     *
     * @param array $options
     *
     * @return array
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function findItemsAssignedToRefundedZedItems(
        array $options = []
    )
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('stockItem')
            ->innerJoin('stockItem.zedOrderItem', 'zedOrderItem')
            ->innerJoin('zedOrderItem.zedOrderItemStatus', 'zedOrderItemStatus')
            ->where('stockItem.status IN (:stockItemStatuses)')
            ->andWhere('zedOrderItemStatus.name IN (:zedOrderItemStatuses)')
            ->setParameter(
                'zedOrderItemStatuses',
                [
                    'refunded',
                    'refund_needed',
                    'refunded_with_credit',
                    'cancel_refunded_credit',
                ],
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            )
            ->setParameter(
                'stockItemStatuses',
                [
                    EnumStockItemStatusType::STATUS_ASSIGNED,
                    EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
                    EnumStockItemStatusType::STATUS_PICKED,
                ],
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            )
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getResult();
    }

    /**
     * Find first by ZedProduct and StockPosition with different DateExpiration and Barcode
     *
     * @param ZedProduct $zedProduct
     * @param StockPosition $stockPosition
     * @param \DateTime $dateExpiration
     * @param $barcode
     *
     * @return StockItem | null
     */
    public function findFirstByZedProductAndPositionWithDifferentDateExpirationAndBarcode(
        ZedProduct $zedProduct,
        StockPosition $stockPosition,
        \DateTime $dateExpiration,
        $barcode
    )
    {
        $query = $this->createQueryBuilder('stockItem')
            ->innerJoin(
                'NatueStockBundle:StockPosition',
                'stockPosition',
                'WITH',
                'stockPosition.id = stockItem.stockPosition'
            )
            ->where('stockItem.zedProduct = :zedProduct')
            ->andWhere('stockPosition.pickable = :pickable')
            ->andWhere('stockItem.stockPosition = :stockPosition')
            ->andWhere('(stockItem.dateExpiration != :dateExpiration OR stockItem.barcode != :barcode)')
            ->setMaxResults(1)
            ->setParameters(
                [
                    'zedProduct'     => $zedProduct,
                    'stockPosition'  => $stockPosition,
                    'dateExpiration' => $dateExpiration->format('Y-m-d'),
                    'barcode'        => $barcode,
                    'pickable'       => true,
                ]
            )
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param ZedOrder $zedOrder
     *
     * @return array
     */
    public function barcodesGroupAndCountWithinOrder(ZedOrder $zedOrder)
    {
        $query = $this->createQueryBuilder('stockItem')
            ->select('stockItem.barcode')
            ->addSelect('COUNT(stockItem.id) AS amount')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.id = stockItem.zedOrderItem'
            )
            ->where('zedOrderItem.zedOrder = :zedOrder')
            ->andWhere('stockItem.status = :status')
            ->groupBy('stockItem.barcode')
            ->setParameters(
                [
                    'zedOrder' => $zedOrder,
                    'status'   => EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
                ]
            )
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get Products average cost QueryBuilder
     *
     * @return QueryBuilder
     */
    public function getProductsAverageCostQueryBuilder()
    {
        $validStatuses = [
            EnumStockItemStatusType::STATUS_READY,
            EnumStockItemStatusType::STATUS_ASSIGNED,
            EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
            EnumStockItemStatusType::STATUS_PICKED,
        ];

        $queryBuilder = $this->createQueryBuilder('stockItem')
            ->addSelect('zedProduct.sku AS sku')
            ->addSelect('zedProduct.name AS name')
            ->addSelect('AVG(purchaseOrderItem.cost) AS costAverage')
            ->innerJoin(
                'NatueStockBundle:PurchaseOrderItem',
                'purchaseOrderItem',
                'WITH',
                'stockItem.purchaseOrderItem = purchaseOrderItem.id'
            )
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'stockItem.zedProduct = zedProduct.id'
            )
            ->where('stockItem.status IN (:stockItemStatuses)')
            ->groupBy('stockItem.zedProduct')
            ->setParameter(
                'stockItemStatuses',
                $validStatuses,
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            );

        return $queryBuilder;
    }

    /**
     * Get ZedProducts average cost
     * within current stock
     *
     * @return array
     */
    public function getProductsAverageCost()
    {
        $query = $this->getProductsAverageCostQueryBuilder()->getQuery();

        return $query->getResult();
    }

    /**
     * @param int $orderId
     *
     * @return QueryBuilder
     */
    public function getOrderStockItemsQuery($orderId)
    {
        $query = $this->createQueryBuilder('stockItem')
            ->addSelect('stockItem.id AS stockItemId')
            ->addSelect('zedProduct.sku AS productSku')
            ->addSelect('zedProduct.name AS productName')
            ->addSelect('stockPosition.name AS positionName')
            ->addSelect('stockItem.barcode AS stockItemBarcode')
            ->addSelect('stockItem.status AS stockItemStatus')
            ->addSelect('zedOrder.id AS zedOrderId')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.id = stockItem.zedOrderItem'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrder',
                'zedOrder',
                'WITH',
                'zedOrder.id = zedOrderItem.zedOrder'
            )
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'zedOrderItem.zedProduct = zedProduct.id'
            )
            ->innerJoin(
                'NatueStockBundle:StockPosition',
                'stockPosition',
                'WITH',
                'stockPosition.id = stockItem.stockPosition'
            )
            ->where('zedOrderItem.zedOrder = :orderId')
            ->setParameters(
                [
                    'orderId' => $orderId,
                ]
            );

        return $query;
    }

    /**
     * Find StockItems related to Order
     *
     * @param int $orderId
     *
     * @return array
     */
    public function findOrderStockItems($orderId)
    {
        $queryBuilder = $this->createQueryBuilder('stockItem')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->where('zedOrderItem.zedOrder = :zedOrder')
            ->setParameter('zedOrder', $orderId)
            ->getQuery();

        return $queryBuilder->getResult();
    }

    /**
     * Count items within order,
     * with specific status
     *
     * @param int $orderId
     * @param array $validStatusList
     *
     * @return int
     */
    public function countOrderItemsWithStatus($orderId, array $validStatusList)
    {
        $queryBuilder = $this->createQueryBuilder('stockItem')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->where('zedOrderItem.zedOrder = :zedOrder')
            ->andWhere('stockItem.status IN (:stockItemStatuses)')
            ->setParameter('zedOrder', $orderId)
            ->setParameter(
                'stockItemStatuses',
                $validStatusList,
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            );

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param ZedOrder $order
     * @return bool
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function isOrderReadyToPack(ZedOrder $order)
    {
        $queryBuilder = $this->createQueryBuilder('stockItem')
            ->select('count(stockItem.id)')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->where('zedOrderItem.zedOrder = :zedOrder')
            ->andWhere('stockItem.status != :status')
            ->setParameter('zedOrder', $order)
            ->setParameter('status', EnumStockItemStatusType::STATUS_PICKED);

        return $queryBuilder->getQuery()->getSingleScalarResult() == 0;
    }

    /**
     * @param ZedOrder $zedOrder
     * @param array $options
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @return array   StockItem Collection
     */
    public function findUnpackedItemsWithinOrder(
        ZedOrder $zedOrder,
        array $options = []
    )
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('stockItem')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'stockItem.zedProduct = zedProduct.id'
            )
            ->where('zedOrderItem.zedOrder = :zedOrder')
            ->andWhere('stockItem.shippingVolume is NULL')
            ->setParameters(
                [
                    'zedOrder' => $zedOrder,
                ]
            )
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getResult();
    }

    /**
     * @param ZedOrder $order
     * @param array $options
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @return array
     */
    public function getAssignedItemsForZedOrder(ZedOrder $order, array $options = [])
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('stockItem')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.id = stockItem.zedOrderItem'
            )
            ->where('zedOrderItem.zedOrder = :orderId')
            ->andWhere('stockItem.status = :status')
            ->setParameters(
                [
                    'orderId' => $order->getId(),
                    'status'  => EnumStockItemStatusType::STATUS_ASSIGNED,
                ]
            )
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return new ArrayCollection($query->getResult());
    }

    /**
     * @param ZedOrder $order
     * @param array $options
     * @return ArrayCollection
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getWaitingForPickingItemsForZedOrder(ZedOrder $order, array $options = [])
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('stockItem')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.id = stockItem.zedOrderItem'
            )
            ->where('zedOrderItem.zedOrder = :orderId')
            ->andWhere('stockItem.status = :status')
            ->setParameters(
                [
                    'orderId' => $order->getId(),
                    'status'  => EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
                ]
            )
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return new ArrayCollection($query->getResult());
    }

    /**
     * @param ZedOrder $order
     * @param array $options
     * @return ArrayCollection
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getPickedItemsForZedOrder(ZedOrder $order, array $options = [])
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('stockItem')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.id = stockItem.zedOrderItem'
            )
            ->where('zedOrderItem.zedOrder = :orderId')
            ->andWhere('stockItem.status = :status')
            ->setParameters(
                [
                    'orderId' => $order->getId(),
                    'status'  => EnumStockItemStatusType::STATUS_PICKED,
                ]
            )
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return new ArrayCollection($query->getResult());
    }

    /**
     * @param $orderId
     * @param array $options
     * @return ArrayCollection
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getReadyForShippingItemsForZedOrder($orderId, array $options = [])
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('stockItem')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.id = stockItem.zedOrderItem'
            )
            ->where('zedOrderItem.zedOrder = :ordersId')
            ->andWhere('stockItem.status = :status')
            ->setParameters(
                [
                    'ordersId' => $orderId,
                    'status'   => EnumStockItemStatusType::STATUS_READY_FOR_SHIPPING,
                ]
            )
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return new ArrayCollection($query->getResult());
    }

    /**
     * @param ZedOrder $zedOrder
     * @param array $options
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @return array
     */
    public function getItemsByZedOrder(ZedOrder $zedOrder, array $options = [])
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('stockItem')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->where('zedOrderItem.zedOrder = :zedOrder')
            ->setParameters(
                [
                    'zedOrder' => $zedOrder,
                ]
            )
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getResult();
    }

    /**
     * @param ZedOrder $zedOrder
     * @param array $options
     *
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @return array
     */
    public function getOrderVolumesItems(
        ZedOrder $zedOrder,
        array $options = []
    )
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('stockItem')
            ->innerJoin(
                'NatueShippingBundle:ShippingVolume',
                'shippingVolume',
                'WITH',
                'stockItem.shippingVolume = shippingVolume.id'
            )
            ->where('shippingVolume.zedOrder = :zedOrder')
            ->setParameters(
                [
                    'zedOrder' => $zedOrder,
                ]
            )
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getResult();
    }

    /**
     * @param InventoryEntity $inventoryEntity
     *
     * @return array
     */
    public function getMissingItemsByInventory(InventoryEntity $inventoryEntity)
    {
        return $this->createQueryBuilder('stockItem')
            ->leftJoin(
                'NatueStockBundle:InventoryItem',
                'inventoryItem',
                'WITH',
                'inventoryItem.stockItem = stockItem.id AND inventoryItem.inventory = :inventoryId'
            )
            ->where('stockItem.status IN (:statuses)')
            ->andWhere('stockItem.stockPosition = :position')
            ->andWhere('inventoryItem.id IS NULL')
            ->setParameter('statuses', [
                EnumStockItemStatusType::STATUS_READY,
                EnumStockItemStatusType::STATUS_ASSIGNED,
                EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
            ])
            ->setParameter('position', $inventoryEntity->getStockPosition()->getId())
            ->setParameter('inventoryId', $inventoryEntity->getId())
            ->getQuery()->getResult();
    }

    /**
     * @param string $sellItemsWithExpirationAt
     * @return QueryBuilder
     */
    protected function getCurrentSellableStockQueryBuilder($sellItemsWithExpirationAt = '+11 days')
    {
        return $this->createQueryBuilder('stockItem')
            ->select('zedProduct.sku AS sku')
            ->addSelect('COUNT(stockItem.id) AS quantity')
            ->innerJoin(
                'stockItem.zedProduct',
                'zedProduct'
            )
            ->leftJoin(
                'stockItem.stockPosition',
                'stockPosition'
            )
            ->innerJoin(
                'stockItem.purchaseOrderItem',
                'purchaseOrderItem'
            )
            ->where(
                '((stockItem.status IN (:stockItemStatuses) AND ' .
                'stockPosition.pickable = :isPickable AND ' .
                'stockPosition.enabled = :isEnabled) OR ' .
                '(stockItem.status IN (:picked)))'
            )
            ->andWhere('(
                stockItem.dateExpiration >= :sellItemsWithExpirationAt
                OR stockItem.dateExpiration = :noExpire
            )')
            ->groupBy('zedProduct.id')
            ->setParameters(
                [
                    'isPickable' => true,
                    'isEnabled'  => true,
                ]
            )
            ->setParameter(
                'picked',
                [EnumStockItemStatusType::STATUS_PICKED, EnumStockItemStatusType::STATUS_READY_FOR_SHIPPING],
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            )
            ->setParameter(
                'stockItemStatuses',
                $this->sellableStatuses,
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            )
            ->setParameter('sellItemsWithExpirationAt', date('Y-m-d', strtotime($sellItemsWithExpirationAt)))
            ->setParameter('noExpire', '0000-00-00');
    }

    /**
     * @param array $zedProducts
     *
     * @return array
     */
    public function findAvailableToAssign(array $zedProducts)
    {
        return $this->createQueryBuilder('stockItem')
            ->select('zedProduct.sku AS sku')
            ->addSelect('COUNT(stockItem.id) AS quantity')
            ->innerJoin(
                'stockItem.zedProduct',
                'zedProduct'
            )
            ->leftJoin(
                'stockItem.stockPosition',
                'stockPosition'
            )
            ->innerJoin(
                'stockItem.purchaseOrderItem',
                'purchaseOrderItem'
            )
            ->where('stockItem.status = :ready')
            ->andWhere('stockPosition.pickable = :isPickable')
            ->andWhere('stockPosition.enabled = :isEnabled')
            ->andWhere('zedProduct.id in (:zedProducts)')
            ->groupBy('zedProduct.id')
            ->setParameters(
                [
                    'isPickable'  => true,
                    'isEnabled'   => true,
                    'ready'       => EnumStockItemStatusType::STATUS_READY,
                    'zedProducts' => $zedProducts
                ]
            )
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Get current sellable Stock data
     *
     * Current stock is defined as count of "stock_items"
     * that status (ready, waiting_for_picking, assigned
     * or picked) and on the "stock_position" that are
     * "pickable" and "enabled
     *
     * @param string $sellItemsWithExpirationAt
     *
     * @return array
     */
    public function getCurrentSellableStockData($sellItemsWithExpirationAt)
    {
        $query = $this
            ->getCurrentSellableStockQueryBuilder($sellItemsWithExpirationAt)
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * Get current sellable Stock data of Zed Product
     *
     * @param ZedProduct $zedProduct
     * @param $sellItemsWithExpirationAt
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCurrentSellableStockDataByZedProduct(ZedProduct $zedProduct, $sellItemsWithExpirationAt)
    {
        $query = $this->getCurrentSellableStockQueryBuilder($sellItemsWithExpirationAt)
            ->addSelect('COUNT(stockItem.id) AS qty')
            ->andWhere('stockItem.zedProduct = :zedProduct')
            ->setParameter('zedProduct', $zedProduct)
            ->getQuery();

        $result = $query->getOneOrNullResult();

        return $result ? $result['qty'] : 0;
    }

    /**
     * @param array $params See below:
     *      $params['new_stock_position_id'] New position's id
     *      $params['old_stock_position_id'] Old position's id
     *      $params['barcode']               Item's barcode to be moved
     *
     * @throws \Exception
     */
    public function changeAllStockItemsForNewPosition($params)
    {
        $this->validate($params);

        $this->createQueryBuilder('NatueStockBundle:stockItem')
            ->update('NatueStockBundle:stockItem stockItem')
            ->set('stockItem.stockPosition', $params['new_stock_position_id'])
            ->where('stockItem.stockPosition = :stockPosition')
            ->andWhere('stockItem.status = :status')
            ->andWhere('stockItem.barcode = :barcode')
            ->setParameters(
                [
                    'barcode'       => $params['barcode'],
                    'stockPosition' => $params['old_stock_position_id'],
                    'status'        => EnumStockItemStatusType::STATUS_READY,
                ]
            )
            ->getQuery()
            ->execute();
    }

    /**
     * @return array|\Natue\Bundle\StockBundle\Entity\StockItem[]
     */
    public function findInvoiceableItems()
    {
        return $this->createQueryBuilder('stockItem')
            ->addSelect('zedProduct')
            ->addSelect('purchaseOrderItem')
            ->addSelect('purchaseOrder')
            ->innerJoin('stockItem.zedProduct', 'zedProduct')
            ->innerJoin('stockItem.purchaseOrderItem', 'purchaseOrderItem')
            ->innerJoin('purchaseOrderItem.purchaseOrder', 'purchaseOrder')
            ->leftJoin('stockItem.invoice', 'invoice')
            ->where('stockItem.status IN (:statuses)')
            ->andWhere('invoice.id IS NULL')
            ->setParameters([
                'statuses' => [
                    EnumStockItemStatusType::STATUS_DAMAGED,
                    EnumStockItemStatusType::STATUS_LOST,
                ],
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array|\Doctrine\Common\Collections\ArrayCollection[]|\Natue\Bundle\StockBundle\Entity\StockItem[][]
     */
    public function findInvoiceableItemsGroupedByPurchaseOrder()
    {
        return array_reduce($this->findInvoiceableItems(), function (array $groups, StockItem $stockItem) {
            $key = $stockItem->getPurchaseOrderItem()->getPurchaseOrder()->getId();

            if (!array_key_exists($key, $groups)) {
                $groups[$key] = new ArrayCollection;
            }

            $groups[$key]->set($stockItem->getId(), $stockItem);

            return $groups;
        }, []);
    }

    /**
     * @param array $stockStatuses
     * @return QueryBuilder
     */
    protected function getCurrentItemsOnStatusesQueryBuilder(array $stockStatuses = [])
    {
        if (!$stockStatuses) {
            $stockStatuses = EnumStockItemStatusType::$values;
        }

        $queryBuilder = $this->createQueryBuilder('stockItem');

        return
            $queryBuilder
                ->innerJoin(
                    'NatueZedBundle:ZedOrderItem',
                    'zedOrderItem',
                    'WITH',
                    'zedOrderItem.id = stockItem.zedOrderItem'
                )
                ->innerJoin(
                    'NatueZedBundle:ZedOrderItemStatus',
                    'zedOrderItemStatus',
                    'WITH',
                    'zedOrderItemStatus.id = zedOrderItem.zedOrderItemStatus'
                )
                ->where($queryBuilder->expr()->in('zedOrderItemStatus.name', ':zedStatuses'))
                ->andWhere($queryBuilder->expr()->in('stockItem.status', ':stockStatuses'))
                ->setParameters([
                    'zedStatuses'   => [
                        EnumZedOrderItemStatusType::STATUS_READY_FOR_PICKING,
                        EnumZedOrderItemStatusType::STATUS_READY_FOR_INVOICE,
                        EnumZedOrderItemStatusType::STATUS_INVOICE_CREATED,
                        EnumZedOrderItemStatusType::STATUS_INVOICE_CREATION_INITIALIZED,
                        EnumZedOrderItemStatusType::STATUS_WAITING_FOR_SHIPPING,
                    ],
                    'stockStatuses' => $stockStatuses
                ]);
    }

    /**
     * @param array $stockStatuses
     * @param int $numOldDays
     * @return array
     */
    public function getLostedOrdersOnStatuses(array $stockStatuses = [], $numOldDays = 0)
    {
        return
            $this->getCurrentItemsOnStatusesQueryBuilder($stockStatuses)
                ->select('zedOrder.id')
                ->addSelect('zedOrder.incrementId')
                ->addSelect('stockItem.status')
                ->addSelect('count(zedOrderItem.id) as numItems')
                ->addSelect('date(max(assigned.createdAt)) as lostedAt')
                ->addSelect('shippingPickingList.id as pickingList')
                ->innerJoin(
                    'NatueZedBundle:ZedOrder',
                    'zedOrder',
                    'WITH',
                    'zedOrderItem.zedOrder = zedOrder.id'
                )
                ->innerJoin(
                    'NatueStockBundle:StockItemStatusHistory',
                    'assigned',
                    'WITH',
                    'stockItem.id = assigned.stockItem and assigned.status = :assigned'
                )
                ->leftJoin(
                    'NatueZedBundle:OrderExtended',
                    'orderExtended',
                    'WITH',
                    'orderExtended.zedOrder = zedOrder.id'
                )
                ->leftJoin(
                    'NatueShippingBundle:ShippingPickingList',
                    'shippingPickingList',
                    'WITH',
                    'orderExtended.shippingPickingList = shippingPickingList.id'
                )
                ->andHaving('max(assigned.createdAt) <= :oldDate')
                ->setParameter('assigned', 'assigned')
                ->setParameter('oldDate', date('Y-m-d', strtotime("-$numOldDays days")))
                ->groupBy('zedOrderItem.zedOrder')
                ->getQuery()
                ->getScalarResult();
    }

    /**
     * @param array $stockStatuses
     *
     * @return array
     */
    public function countCurrentItemsOnStatuses(array $stockStatuses = [])
    {
        return
            $this->getCurrentItemsOnStatusesQueryBuilder($stockStatuses)
                ->select('stockItem.status')
                ->addSelect('count(distinct zedOrderItem.zedOrder) as total')
                ->groupBy('stockItem.status')
                ->getQuery()
                ->getScalarResult();
    }

    /**
     * @param array $params
     * @return void
     * @throws \Exception
     */
    private function validate(array $params)
    {
        if (empty($params['barcode'])) {
            throw new \Exception("Barcode is required!");
        }

        if (!$params['old_stock_position_id']) {
            throw new \Exception('"Old" position not found');
        }

        if (!$params['new_stock_position_id']) {
            throw new \Exception('"New" position not found');
        }

        if ($params['old_stock_position_id'] == $params['new_stock_position_id']) {
            throw new \Exception('Position "from" and Position "to" are the same');
        }

        $this->validateIfItemsIsPermitedForNewPosition($params);
    }

    /**
     * @param array $params
     * @return void
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Exception
     */
    private function validateIfItemsIsPermitedForNewPosition(array $params)
    {
        $stockItem = $this->createQueryBuilder('stockItem')
            ->innerJoin('stockItem.zedProduct', 'zedProduct')
            ->where('zedProduct.sku = :sku')
            ->andWhere('stockItem.stockPosition = :stockPosition')
            ->setParameters(
                [
                    'sku'           => $params['sku'],
                    'stockPosition' => $params['new_stock_position_id'],
                ]
            )
            ->getQuery()
            ->getOneOrNullResult();

        if (!empty($stockItem)) {
            if ($stockItem->getBarcode() != $params['barcode']) {
                throw new \Exception('Position already have the same product with different barcode.');
            }

            if ($stockItem->getDateExpiration()->format('Y-m-d') != $params['dateExpiration']) {
                throw new \Exception('Position already have the same product with different expiration date.');
            }
        }
    }
}
