<?php

namespace Natue\Bundle\ZedBundle\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityRepository;

use Natue\Bundle\ShippingBundle\Entity\ShippingLogisticsProvider;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;

/**
 * @package Natue\Bundle\ZedBundle\Repository
 */
class ZedOrderRepository extends EntityRepository
{
    const READY_FOR_INVOICE = 111;
    const MONO_SKU_LIMIT = 100;

    /**
     * @return array
     */
    private function getDefaultOptions()
    {
        return [
            'withLock' => true
        ];
    }

    /**
     * "broken" items should be included as well good ones
     * @param $orderIdsList
     * @return mixed
     */
    public function getNotExtendedOrdersWithinIdsList($orderIdsList)
    {
        $query = $this->createQueryBuilder('zedOrder')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.zedOrder = zedOrder.id'
            )
            ->leftJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->where('stockItem.status = :status')
            ->andWhere('zedOrder.incrementId IN (:orderIncrementIds)')
            ->setParameters(
                [
                    'orderIncrementIds' => $orderIdsList,
                    'status'            => EnumStockItemStatusType::STATUS_ASSIGNED
                ]
            )
            ->getQuery();

        return $query->getResult();
    }

    public function getOldestAssignedForPickingOrdersByProviderWithLimit(
        ShippingLogisticsProvider $provider,
        $requestedOrderAmount,
        $zedPickableStatuses,
        array $options = []
    ) {
        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('zedOrder')
            ->addSelect('MIN(zedOrderItem.updatedAt) AS HIDDEN orderUpdatedAt')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.zedOrder = zedOrder.id'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'pickableOrderItem',
                'WITH',
                'pickableOrderItem.zedOrder = zedOrder.id AND pickableOrderItem.zedOrderItemStatus IN (:zedPickableStatuses)'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrderItemStatus',
                'zedOrderItemStatus',
                'WITH',
                'zedOrderItem.zedOrderItemStatus = zedOrderItemStatus.id'
            )
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id AND stockItem.status = :assigned'
            )
            ->innerJoin(
                'NatueShippingBundle:ShippingTariff',
                'shippingTariff',
                'WITH',
                'zedOrder.shippingTariffCode = shippingTariff.id'
            )
            ->leftJoin(
                'NatueZedBundle:OrderExtended',
                'orderExtended',
                'WITH',
                'orderExtended.zedOrder = zedOrder.id'
            )

            ->innerJoin(
                'NatueZedBundle:ZedOrderItemStatusHistory',
                'readyForInvoice',
                'WITH',
                'zedOrderItem.id = readyForInvoice.zedOrderItem AND readyForInvoice.zedOrderItemStatus = :readyForInvoice'
            )
            ->where('shippingTariff.logisticsProvider = :provider')
            ->andWhere('orderExtended.zedOrder IS NULL')
            ->andWhere('zedOrder.invoiceKey IS NOT NULL')
            ->groupBy('zedOrder.id')
            ->orderBy('readyForInvoice.createdAt')
            ->having('COUNT(DISTINCT pickableOrderItem.id) = COUNT(DISTINCT stockItem.id)')
            ->andHaving('COUNT(DISTINCT pickableOrderItem.id) > 0');

        if ($options['isMonoSku']) {
            $query = $query->leftJoin(
                    'NatueZedBundle:ZedProduct',
                    'zedProduct',
                    'WITH',
                    'zedProduct.id = zedOrderItem.zedProduct'
                )
                ->andHaving('COUNT(distinct zedProduct.sku) = 1');
        }

        if (!$options['isMonoSku']) {
            $query = $query->setMaxResults($requestedOrderAmount);
        } else {
            $query = $query->setMaxResults(self::MONO_SKU_LIMIT);
        }

        $query = $query->setParameter(
                'zedPickableStatuses',
                $zedPickableStatuses,
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            )
            ->setParameter('provider', $provider)
            ->setParameter('assigned', EnumStockItemStatusType::STATUS_ASSIGNED)
            ->setParameter('readyForInvoice', self::READY_FOR_INVOICE)
            ->getQuery();

        if ($options['withLock']) {
            $query = $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }


        return $query->getResult();
    }

    public function getDataArrayForExpeditionLabels(array $shippingPickingListId)
    {
        $query = $this->createQueryBuilder('zedOrder')
            ->select("CONCAT_WS(' ', zedOrder.customerFirstname, zedOrder.customerLastname) AS customerFullname")
            ->addSelect("CONCAT_WS(', ', zedOrder.customerAddress1, zedOrder.customerAddress2) AS addressLine1")
            ->addSelect('zedOrder.customerAdditional AS customerAdditional')
            ->addSelect('zedOrder.customerQuarter AS quarter')
            ->addSelect('zedOrder.customerCity AS city')
            ->addSelect('zedOrder.customerState AS state')
            ->addSelect('zedOrder.customerZipcode AS zipcode')
            ->addSelect('zedOrder.customerAddressReference AS addressReference')
            ->addSelect('shippingTariff.name AS tariffName')
            ->addSelect('zedOrder.incrementId AS incrementId')
            ->innerJoin(
                'NatueZedBundle:OrderExtended',
                'orderExtended',
                'WITH',
                'orderExtended.zedOrder = zedOrder.id'
            )
            ->innerJoin(
                'NatueShippingBundle:ShippingTariff',
                'shippingTariff',
                'WITH',
                'shippingTariff.id = zedOrder.shippingTariffCode'
            )
            ->where('zedOrder.incrementId IN (:shippingPickingList)')
            ->setParameter(
                'shippingPickingList',
                $shippingPickingListId,
                Connection::PARAM_STR_ARRAY
            )
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @param array $incrementIds
     *
     * @return array
     */
    public function getCustomerInformationForOrders(array $incrementIds)
    {
        $query = $this->createQueryBuilder('zedOrder')
            ->select('zedOrder.incrementId')
            ->addSelect("CONCAT_WS(' ', zedOrder.customerFirstname, zedOrder.customerLastname) AS customerName")
            ->where('zedOrder.incrementId IN (:incrementIds)')
            ->setParameter(
                'incrementIds',
                $incrementIds,
                Connection::PARAM_STR_ARRAY
            )
            ->getQuery();

        return $query->getArrayResult();
    }

    public function getCustomerName($incrementId)
    {
        return $this->createQueryBuilder('zedOrder')
                    ->addSelect("CONCAT_WS(' ', zedOrder.customerFirstname, zedOrder.customerLastname) AS customerName")
                    ->where('zedOrder.incrementId = :incrementId')
                    ->setParameter('incrementId', $incrementId)
                    ->getQuery()
                    ->getSingleResult();
    }

    /**
     * @param array $incrementIds
     *
     * @return array
     */
    public function getCustomerInformationForMonoSku(array $incrementIds)
    {
        $query = $this->createQueryBuilder('zedOrder')
            ->select('zedOrder.incrementId')
            ->addSelect("CONCAT_WS(' ', zedOrder.customerFirstname, zedOrder.customerLastname) AS customerName")
            ->addSelect("COUNT('zedOrderItem') AS totalItems")
            ->addSelect("zedProduct.name AS productName")
            ->where('zedOrder.incrementId IN (:incrementIds)')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.zedOrder = zedOrder.id'
            )
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'zedProduct.id = zedOrderItem.zedProduct'
            )
            ->groupBy('zedOrder.incrementId')
            ->setParameter(
                'incrementIds',
                $incrementIds,
                Connection::PARAM_STR_ARRAY
            )
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @param int $logisticsProviderId
     *
     * @return array
     */
    public function findOrdersForShipping($logisticsProviderId)
    {
        $query = $this->createQueryBuilder('zedOrder')
            ->innerJoin(
                'NatueShippingBundle:ShippingTariff',
                'shippingTariff',
                'WITH',
                'shippingTariff.id = zedOrder.shippingTariffCode'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.zedOrder = zedOrder.id'
            )
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->where('shippingTariff.logisticsProvider = :logisticsProvider')
            ->groupBy('zedOrder.id')
            ->having('SUM(CASE stockItem.status WHEN :status THEN 1 ELSE 0 END) = COUNT(stockItem.id)')
            ->setParameters(
                [
                    'logisticsProvider' => $logisticsProviderId,
                    'status'            => EnumStockItemStatusType::STATUS_PICKED,
                ]
            )
            ->getQuery();

        return $query->getResult();
    }

    /**
     * @param int $logisticsProviderId
     *
     * @return array
     */
    public function getOrdersReadyForShippingByLogisticsProvider($logisticsProviderId)
    {
        $query = $this->createQueryBuilder('zedOrder')
            ->select('zedOrder.id AS orderId')
            ->addSelect('stockItem.id AS stockItemId')
            ->addSelect('IDENTITY(shippingTariff.logisticsProvider) AS logisticsProviderId')
            ->addSelect('shippingVolume.trackingCode AS trackingCode')
            ->innerJoin(
                'NatueShippingBundle:ShippingVolume',
                'shippingVolume',
                'WITH',
                'shippingVolume.zedOrder = zedOrder.id'
            )
            ->innerJoin(
                'NatueShippingBundle:ShippingPackage',
                'shippingPackage',
                'WITH',
                'shippingPackage.id = shippingVolume.shippingPackage'
            )
            ->innerJoin(
                'NatueShippingBundle:ShippingTariff',
                'shippingTariff',
                'WITH',
                'shippingTariff.id = zedOrder.shippingTariffCode'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.zedOrder = zedOrder.id'
            )
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->where('stockItem.status = :status')
            ->andWhere('shippingTariff.logisticsProvider = :logisticsProvider')
            ->setParameters(
                [
                    'status'            => EnumStockItemStatusType::STATUS_READY_FOR_SHIPPING,
                    'logisticsProvider' => $logisticsProviderId
                ]
            )
            ->getQuery();

        return $query->getScalarResult();
    }

    public function isBarcodeHasStockStatus(ZedOrder $zedOrder, $barcode, $status)
    {
        $query = $this->createQueryBuilder('zedOrder')
            ->select("COUNT(stockItem.barcode) as total")
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.zedOrder = zedOrder.id'
            )
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->where('zedOrder.id = :zedOrder')
            ->andWhere('stockItem.status = :status')
            ->andWhere('stockItem.barcode = :barcode')
            ->setParameters(
                [
                    'zedOrder' => $zedOrder,
                    'barcode'  => (int) $barcode,
                    'status'   => $status,
                ]
            )
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getTotalProductsForZedOrderAndBarcode(ZedOrder $zedOrder, $barcode)
    {
        $query = $this->createQueryBuilder('zedOrder')
            ->select("COUNT(stockItem.barcode) as total")
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.zedOrder = zedOrder.id'
            )
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->where('zedOrder.id = :zedOrder')
            ->andWhere('stockItem.barcode = :barcode')
            ->setParameters(
                [
                    'barcode'  => (int) $barcode,
                    'zedOrder' => $zedOrder,
                ]
            )
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param string   $barcode
     * @param ZedOrder $zedOrder
     *
     * @return array
     */
    public function getOrderByItemBarcodeAndPk($barcode, ZedOrder $zedOrder)
    {
        $query = $this->createQueryBuilder('zedOrder')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.zedOrder = zedOrder.id'
            )
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->where('zedOrder.id = :zedOrder')
            ->andWhere('stockItem.barcode = :barcode')
            ->setParameters(
                [
                    'barcode'  => (int) $barcode,
                    'zedOrder' => $zedOrder,
                ]
            )
            ->getQuery();

        return $query->getResult();
    }

    public function getPickingListTodayByLogisticProvider()
    {
        $query = $this->createQueryBuilder('zedOrder')
            ->select("shippingLogisticsProvider.nameInternal")
            ->addSelect("COUNT(zedOrder.id) AS total")
            ->leftJoin(
                'NatueShippingBundle:ShippingTariff',
                'shippingTariff',
                'WITH',
                'zedOrder.shippingTariffCode = shippingTariff.id'
            )
            ->leftJoin(
                'NatueShippingBundle:ShippingLogisticsProvider',
                'shippingLogisticsProvider',
                'WITH',
                'shippingTariff.logisticsProvider = shippingLogisticsProvider.id'
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
            ->where("date(shippingPickingList.createdAt) = :date")
            ->setParameter("date", date("Y-m-d"))
            ->groupBy('shippingLogisticsProvider.nameInternal')
            ->getQuery();

        return $query->getArrayResult();
    }

    public function findOrdersWithoutStockAssignment(
        $limit = 100,
        array $options = [],
        array $lockedOrders = []
    )
    {
        $options = array_merge($this->getDefaultOptions(), $options);

        $queryBuilder = $this->createOrdersWithoutStockAssignmentsQueryBuilder(
            ['121', '101'], // clarify_picking_failed, ready_for_picking
            $limit
        );

        if ($lockedOrders) {
            $queryBuilder
                ->andWhere('zedOrder.id not in (:lockedOrders)')
                ->setParameter('lockedOrders', $lockedOrders, Connection::PARAM_STR_ARRAY);
        }

        $query = $queryBuilder->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        $result = $query->getResult();
        return $result;
    }

    protected function createOrdersWithoutStockAssignmentsQueryBuilder(array $statuses, $limit)
    {
        return $this->createQueryBuilder('zedOrder')
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'pickable',
                'WITH',
                'zedOrder.id = pickable.zedOrder and pickable.zedOrderItemStatus in (:statuses)'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrder.id = zedOrderItem.zedOrder'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrderItemStatusHistory',
                'readyForInvoice',
                'WITH',
                'readyForInvoice.zedOrderItem = zedOrderItem.id AND readyForInvoice.zedOrderItemStatus = 111'
            )
            ->leftJoin('NatueStockBundle:StockItem', 'stockItem', 'WITH', 'stockItem.zedOrderItem = zedOrderItem.id')
            ->where('zedOrder.invoiceKey is not null')
            ->setMaxResults($limit)
            ->setParameter(
                'statuses',
                $statuses,
                Connection::PARAM_STR_ARRAY
            )
            ->orderBy('readyForInvoice.createdAt')
            ->groupBy('zedOrder.id')
            ->having('COUNT(DISTINCT stockItem.id) = 0')
        ;
    }
}
