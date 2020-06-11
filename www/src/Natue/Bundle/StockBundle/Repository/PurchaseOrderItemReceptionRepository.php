<?php

namespace Natue\Bundle\StockBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;

/**
 * @package Natue\Bundle\StockBundle\Repository
 */
class PurchaseOrderItemReceptionRepository extends EntityRepository
{
    /**
     * @param PurchaseOrder $purchaseOrder
     * @return PurchaseOrderItemReception
     */
    public function getReceivedPurchaseOrderItemReception(PurchaseOrder $purchaseOrder, ZedProduct $zedProduct)
    {
        $queryBuilder = $this->createQueryBuilder('purchaseOrderItemReception')
            ->innerJoin(
                'NatueStockBundle:PurchaseOrderItem',
                'purchaseOrderItem',
                'WITH',
                'purchaseOrderItem.purchaseOrderItemReception = purchaseOrderItemReception.id'
            )
            ->where('purchaseOrderItemReception.purchaseOrder = :purchaseOrder')
            ->andWhere('purchaseOrderItem.zedProduct = :zedProduct')
            ->andWhere('purchaseOrderItem.status = :status')
            ->orderBy('purchaseOrderItemReception.createdAt', 'ASC')
            ->setParameters(
                [
                    'purchaseOrder' => $purchaseOrder,
                    'zedProduct'    => $zedProduct,
                    'status'        => EnumPurchaseOrderItemStatusType::STATUS_RECEIVED
                ]
            );

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @param User          $user
     * @return mixed
     */
    public function getInProgressUserPurchaseOrderItemReception(PurchaseOrder $purchaseOrder, User $user)
    {
        $queryBuilder = $this->createQueryBuilder('purchaseOrderItemReception')
            ->leftJoin(
                'NatueStockBundle:PurchaseOrderItem',
                'purchaseOrderItem',
                'WITH',
                'purchaseOrderItem.purchaseOrderItemReception = purchaseOrderItemReception.id'
            )
            ->where('purchaseOrderItemReception.purchaseOrder = :purchaseOrder')
            ->andWhere('purchaseOrderItemReception.user = :user')
            ->andWhere('purchaseOrderItem.status = :status OR purchaseOrderItem.id IS NULL')
            ->setMaxResults(1)
            ->setParameters(
                [
                    'purchaseOrder' => $purchaseOrder,
                    'user'          => $user,
                    'status'        => EnumPurchaseOrderItemStatusType::STATUS_RECEIVING
                ]
            );

        $result = $queryBuilder->getQuery()->getResult();

        if (count($result) == 1) {
            return $result[0];
        }

        return null;
    }

    /**
     * Get current distributed items count
     *
     * @param PurchaseOrder $purchaseOrder
     * @param User          $user
     * @param int           $zedProductId
     *
     * @return array
     */
    public function getCurrentDistributedItemsCount(
        PurchaseOrder $purchaseOrder,
        User $user,
        $zedProductId
    ) {

        $queryBuilder = $this->createQueryBuilder('purchaseOrderItemReception')
            ->select('stockPosition.name AS positionName')
            ->addSelect('stockItem.barcode AS stockItemBarcode')
            ->addSelect('stockItem.dateExpiration')
            ->addSelect('COUNT(stockItem.id) AS receivingQuantity')
            ->leftJoin(
                'NatueStockBundle:PurchaseOrderItem',
                'purchaseOrderItem',
                'WITH',
                'purchaseOrderItem.purchaseOrderItemReception = purchaseOrderItemReception.id'
            )
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.purchaseOrderItem = purchaseOrderItem.id'
            )
            ->innerJoin(
                'NatueStockBundle:StockPosition',
                'stockPosition',
                'WITH',
                'stockItem.stockPosition = stockPosition.id'
            )
            ->where('purchaseOrderItemReception.user = :user')
            ->andWhere('purchaseOrderItemReception.purchaseOrder = :purchaseOrder')
            ->andWhere('purchaseOrderItem.zedProduct = :zedProduct')
            ->andWhere('purchaseOrderItem.status = :purchaseOrderItemStatus')
            ->andWhere('stockItem.status = :stockItemStatus')
            ->groupBy('stockPosition.id')
            ->addGroupBy('stockItem.barcode')
            ->addGroupBy('stockItem.dateExpiration')
            ->setParameters(
                [
                    'purchaseOrder'           => $purchaseOrder,
                    'user'                    => $user,
                    'zedProduct'              => $zedProductId,
                    'purchaseOrderItemStatus' => EnumPurchaseOrderItemStatusType::STATUS_RECEIVING,
                    'stockItemStatus'         => EnumStockItemStatusType::STATUS_INCOMING,
                ]
            )
            ->getQuery();

        return $queryBuilder->getArrayResult();
    }
}
