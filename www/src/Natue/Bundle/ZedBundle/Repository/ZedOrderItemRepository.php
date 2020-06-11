<?php

namespace Natue\Bundle\ZedBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\LockMode;

use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;

/**
 * Class ZedOrderItemRepository
 *
 * @package Natue\Bundle\ZedBundle\Repository
 */
class ZedOrderItemRepository extends EntityRepository
{
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
     * Find "ready_for_picking" items without Stock assignment
     *
     * @param int $limit
     * @param array $options
     *
     * @return array
     */
    public function findReadyForPickingItemsWithoutStockAssignment(
        $limit = 100,
        array $options = []
    )
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createItemsWithoutStockAssignmentsQueryBuilder(['ready_for_picking'], $limit)
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getResult();
    }

    public function findClarifyPickingFailedItemsWithoutStockAssignment(
        $limit = 100,
        array $options = []
    )
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createItemsWithoutStockAssignmentsQueryBuilder(['clarify_picking_failed'], $limit)
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getResult();
    }

    protected function createItemsWithoutStockAssignmentsQueryBuilder(array $statuses, $limit)
    {
        return $this->createQueryBuilder('zedOrderItem')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItemStatus',
                'zedOrderItemStatus',
                'WITH',
                'zedOrderItem.zedOrderItemStatus = zedOrderItemStatus.id'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrderItemStatusHistory',
                'readyForInvoice',
                'WITH',
                'readyForInvoice.zedOrderItem = zedOrderItem.id AND readyForInvoice.zedOrderItemStatus = 111'
            )
            ->leftJoin('NatueStockBundle:StockItem', 'stockItem', 'WITH', 'stockItem.zedOrderItem = zedOrderItem.id')
            ->innerJoin('NatueZedBundle:ZedOrder', 'zedOrder', 'WITH', 'zedOrder.id = zedOrderItem.zedOrder')
            ->where('zedOrderItemStatus.name IN (:statuses)')
            ->andWhere('zedOrder.invoiceKey is not null')
            ->andWhere('stockItem.id is null')
            ->setMaxResults($limit)
            ->setParameter(
                'statuses',
                $statuses,
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            )
            ->orderBy('readyForInvoice.createdAt');
    }

    /**
     * @param ZedOrder $zedOrder
     * @param array $validStatuses
     * @return mixed
     */
    public function countStatusesWithinGroup(ZedOrder $zedOrder, array $validStatuses)
    {
        $query = $this->createQueryBuilder('zedOrderItem')
            ->select('SUM(CASE WHEN stockItem.status IN (:stockStatuses) THEN 1 ELSE 0 END) AS totalStockItemPacked')
            ->addSelect(
                'SUM(
                CASE WHEN zedOrderItemStatus.name IN (:statuses)
                THEN 1
                ELSE 0
                END
                ) AS totalZedOrderItemTaken'
            )
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'zedOrderItem.id = stockItem.zedOrderItem'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrderItemStatus',
                'zedOrderItemStatus',
                'WITH',
                'zedOrderItem.zedOrderItemStatus = zedOrderItemStatus.id'
            )
            ->where('zedOrderItem.zedOrder = :zedOrder')
            ->groupBy('zedOrderItem.zedOrder')
            ->setParameter(
                'stockStatuses',
                [
                    EnumStockItemStatusType::STATUS_PICKED,
                    EnumStockItemStatusType::STATUS_SOLD,
                    EnumStockItemStatusType::STATUS_READY_FOR_SHIPPING,
                ],
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            )
            ->setParameter(
                'statuses',
                $validStatuses,
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            )
            ->setParameter('zedOrder', $zedOrder)
            ->getQuery();

        return $query->getSingleResult();
    }

    /**
     * @param ZedOrder $zedOrder
     * @return int
     */
    public function getTotalItemsForZedOrder(ZedOrder $zedOrder)
    {
        $query = $this->createQueryBuilder('zedOrderItem')
            ->select('count(zedOrderItem.id) as total')
            ->where('zedOrderItem.zedOrder = :zedOrderId')
            ->setParameters(
                [
                    'zedOrderId' => $zedOrder->getId(),
                ]
            )->getQuery();

        return (int)$query->getSingleResult()['total'];
    }

    /**
     * @param ZedOrder $zedOrder
     *
     * @return array
     */
    public function getItemsLeftForVolume(ZedOrder $zedOrder)
    {
        $query = $this->createQueryBuilder('zedOrderItem')
            ->select('zedProduct.id AS zed_product_id')
            ->addSelect('zedProduct.sku AS sku')
            ->addSelect('COUNT(zedOrderItem.id) AS amount_left')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItemStatus',
                'zedOrderItemStatus',
                'WITH',
                'zedOrderItem.zedOrderItemStatus = zedOrderItemStatus.id'
            )
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'zedOrderItem.zedProduct = zedProduct.id'
            )
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->where('zedOrderItem.zedOrder = :zedOrder')
            ->andWhere('zedOrderItemStatus.name = :zedOrderItemStatus')
            ->andWhere('stockItem.shippingVolume is null')
            ->groupBy('zedProduct.id')
            ->setParameters(
                [
                    'zedOrder'           => $zedOrder,
                    'zedOrderItemStatus' => 'invoice_created',
                ]
            )->getQuery();

        return $query->getArrayResult();
    }

    public function getPickingListByLogisticProvider($logisticsProvider)
    {
        $query = $this->createQueryBuilder('zedOrderItem')
            ->select('DATE(shippingPickingList.createdAt) AS dateList')
            ->addSelect('shippingPickingList.id AS idList')
            ->addSelect('DATE(zedOrderItemStatusHistory.createdAt) AS dateReadyPicking')
            ->addSelect('shippingLogisticsProvider.nameInternal AS logisticsProvider')
            ->addSelect('user.username')
            ->addSelect('zedOrder.incrementId')
            ->addSelect("CONCAT_WS(' ', zedOrder.customerFirstname, zedOrder.customerLastname) AS customerName")
            ->addSelect('zedOrder.pickingObservation')
            ->leftJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->leftJoin(
                'NatueZedBundle:ZedOrder',
                'zedOrder',
                'WITH',
                'zedOrder.id = zedOrderItem.zedOrder'
            )
            ->leftJoin(
                'NatueShippingBundle:ShippingTariff',
                'shippingTariff',
                'WITH',
                'shippingTariff.id = zedOrder.shippingTariffCode'
            )
            ->leftJoin(
                'NatueShippingBundle:ShippingLogisticsProvider',
                'shippingLogisticsProvider',
                'WITH',
                'shippingLogisticsProvider.id = shippingTariff.logisticsProvider'
            )
            ->leftJoin(
                'NatueZedBundle:ZedOrderItemStatusHistory',
                'zedOrderItemStatusHistory',
                'WITH',
                'zedOrderItemStatusHistory.zedOrderItem = zedOrderItem.id and zedOrderItemStatusHistory.zedOrderItemStatus = 101'
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
            ->leftJoin(
                'NatueUserBundle:User:User',
                'user',
                'WITH',
                'shippingPickingList.user = user.id'
            )
            ->where('stockItem.status = :waiting_for_picking')
            ->andWhere('shippingLogisticsProvider.nameInternal = :name')
            ->setParameters([
                'waiting_for_picking' => EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
                'name'                => $logisticsProvider,
            ])
            ->groupBy('zedOrder.incrementId')
            ->orderby('dateList')
            ->getQuery();

        return $query->getArrayResult();
    }

    protected function createLastOldDatesStockItemsQueryBuilder(array $stockStatuses, $numDays, $groupBy = null)
    {
        if (!$stockStatuses) {
            $stockStatuses = [
                EnumStockItemStatusType::STATUS_ASSIGNED,
                EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
            ];
        }

        $stockStatuses = implode(',', array_map(function ($status) {
            return sprintf('"%s"', $status);
        }, $stockStatuses));

        $fromDate = $this->createFromDate($numDays);

        $queryBuilder = $this->getEntityManager()
            ->getConnection()
            ->createQueryBuilder()
            ->from('zed_order_item', 'zedOrderItem')
            ->select('DATE(stockItemStatusHistory.created_at) as historyDate')
            ->addSelect('date(readyForInvoice.created_at) as readyForInvoiceDate')
            ->addSelect('stockItem.status')
            ->addSelect('shippingLogisticsProvider.name_internal as provider')
            ->innerJoin(
                'zedOrderItem',
                'stock_item',
                'stockItem',
                'stockItem.zed_order_item = zedOrderItem.id'
            )
            ->innerJoin(
                'zedOrderItem',
                'zed_order_item_status',
                'zedOrderItemStatus',
                'zedOrderItemStatus.id = zedOrderItem.zed_order_item_status'
            )
            ->innerJoin(
                'zedOrderItem',
                'zed_order',
                'zedOrder',
                'zedOrder.id = zedOrderItem.zed_order'
            )
            ->leftJoin(
                'zedOrder',
                'shipping_tariff',
                'shippingTariff',
                'shippingTariff.id = zedOrder.shipping_tariff_code'
            )
            ->innerJoin(
                'shippingTariff',
                'shipping_logistics_provider',
                'shippingLogisticsProvider',
                'shippingLogisticsProvider.id = shippingTariff.logistics_provider'
            )
            ->leftJoin(
                'stockItem',
                'stock_item_status_history',
                'stockItemStatusHistory',
                'stockItemStatusHistory.stock_item = stockItem.id and stockItemStatusHistory.status = stockItem.status'
            )
            ->leftJoin(
                'zedOrderItem',
                'zed_order_item_status_history',
                'readyForInvoice',
                'readyForInvoice.zed_order_item = zedOrderItem.id and readyForInvoice.zed_order_item_status = 261'
            )
            ->leftJoin(
                'stockItem',
                'stock_item_status_history',
                'assigned',
                'assigned.stock_item = stockItem.id and assigned.status = :assigned'
            )
            ->where("stockItem.status in ($stockStatuses)")
            ->andWhere("zedOrderItemStatus.name in ('ready_for_picking', 'ready_for_invoice', 'invoice_created', 'invoice_creation_initialized', 'waiting_for_shipping')")
            ->andHaving('DATE(max(stockItemStatusHistory.created_at)) >= :date')
            ->setParameters([
                ':date' => $fromDate,
                ':assigned' => 'assigned'
            ])
            ->orderBy('readyForInvoice.created_at');

        if ($groupBy) {
            $queryBuilder->groupBy($groupBy);
        }

        return $queryBuilder;
    }

    private function createFromDate($numDays = 5)
    {
        return date('Y-m-d', strtotime("-$numDays days"));
    }

    public function getLastOldDatesStockItems(array $stockStatuses = [], $numDays = 5)
    {
        return $this->createLastOldDatesStockItemsQueryBuilder($stockStatuses, $numDays, 'zedOrder.increment_id')
            ->addSelect('zedOrder.increment_id')
            ->addSelect('zedOrderItem.zed_order')
            ->addSelect('shippingLogisticsProvider.name_official as provider_name')
            ->addSelect('operator.name as operator_name')
            ->addSelect('pickingList.id as picking_list')
            ->addSelect('date(readyForInvoice.created_at) as ready_for_invoice_at')
            ->addSelect('date(max(assigned.created_at)) as assigned_at')
            ->leftJoin(
                'zedOrder',
                'order_extended',
                'orderExtended',
                'zedOrder.id = orderExtended.zed_order_id'
            )
            ->leftJoin(
                'orderExtended',
                'shipping_picking_list',
                'pickingList',
                'pickingList.id = orderExtended.shipping_picking_list'
            )
            ->leftJoin(
                'pickingList',
                'user',
                'operator',
                'operator.id = pickingList.user'
            )
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getLastOldDatesStockItemsTotals(array $stockStatuses = [], $numDays = 5)
    {
        $monoSkuOrdersQueryBuilder = $this->getEntityManager()
            ->getConnection()
            ->createQueryBuilder()
            ->from('zed_order_item', 'zedOrderItem')
            ->select('zedOrderItem.zed_order')
            ->innerJoin(
                'zedOrderItem',
                'stock_item',
                'stockItem',
                'stockItem.zed_order_item = zedOrderItem.id'
            )
            ->innerJoin(
                'zedOrderItem',
                'zed_product',
                'zedProduct',
                'zedProduct.id = zedOrderItem.zed_product'
            )
            ->where('zedOrderItem.zed_order_item_status = 101')
            ->groupBy('zedOrderItem.zed_order')
            ->having('count(distinct zedProduct.sku) = 1');

        return $this->createLastOldDatesStockItemsQueryBuilder($stockStatuses, $numDays, 'historyDate')
            ->addSelect('COUNT(distinct zedOrder.id) as total')
            ->addSelect('COUNT(distinct monoSkuOrders.zed_order) as monoSku')
            ->leftJoin(
                'zedOrder',
                sprintf('(%s)', $monoSkuOrdersQueryBuilder->getSQL()),
                'monoSkuOrders',
                'zedOrder.id = monoSkuOrders.zed_order'
            )
            ->addGroupBy('shippingLogisticsProvider.name_internal')
            ->addGroupBy('stockItem.status')
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getTotalItemByProviderList()
    {
        $query = $this->createQueryBuilder('zedOrderItem')
            ->select('shippingLogisticsProvider.nameInternal as provider')
            ->addSelect('stockItem.status')
            ->addSelect('COUNT(distinct zedOrder.id) as total')
            ->leftJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->leftJoin(
                'NatueZedBundle:ZedOrder',
                'zedOrder',
                'WITH',
                'zedOrder.id = zedOrderItem.zedOrder'
            )
            ->leftJoin(
                'NatueShippingBundle:ShippingTariff',
                'shippingTariff',
                'WITH',
                'shippingTariff.id = zedOrder.shippingTariffCode'
            )
            ->leftJoin(
                'NatueShippingBundle:ShippingLogisticsProvider',
                'shippingLogisticsProvider',
                'WITH',
                'shippingLogisticsProvider.id = shippingTariff.logisticsProvider'
            )
            ->leftJoin(
                'NatueZedBundle:ZedOrderItemStatusHistory',
                'zedOrderItemStatusHistory',
                'WITH',
                'zedOrderItemStatusHistory.zedOrderItem = zedOrderItem.id and zedOrderItemStatusHistory.zedOrderItemStatus = 101'
            )
            ->where('stockItem.status IN (:stockStatuses)')
            ->andWhere('shippingLogisticsProvider.nameInternal IS NOT NULL')
            ->addGroupBy('shippingLogisticsProvider.nameInternal')
            ->addGroupBy('stockItem.status')
            ->setParameters([
                'stockStatuses' => [
                    EnumStockItemStatusType::STATUS_ASSIGNED,
                    EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
                ],
            ],
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            )
            ->getQuery();

        return $query->getScalarResult();
    }
}
