<?php

namespace Natue\Bundle\StockBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItemReception;

/**
 * @package Natue\Bundle\StockBundle\Repository
 */
class PurchaseOrderRepository extends EntityRepository
{
    /**
     * FindOneBy InvoiceKey OR PurchaseOrderId
     * and make sure, that there's at least one item within
     * @param $reference
     * @return mixed
     */
    public function findOneByInvoiceKeyOrPurchaseOrderId($reference)
    {
        $queryBuilder = $this->createQueryBuilder('purchaseOrder')
            ->leftJoin(
                'NatueStockBundle:PurchaseOrderItem',
                'purchaseOrderItem',
                'WITH',
                'purchaseOrderItem.purchaseOrder = purchaseOrder.id'
            )
            ->where('purchaseOrder.invoiceKey = :invoiceKey OR purchaseOrder.id = :purchaseOrderId')
            ->andWhere('purchaseOrderItem.status <> :deleted')
            ->groupBy('purchaseOrder.id')
            ->having('COUNT(purchaseOrderItem.id) > 0')
            ->setParameters(
                [
                    'invoiceKey'      => $reference,
                    'purchaseOrderId' => $reference,
                    'deleted'         => EnumPurchaseOrderItemStatusType::STATUS_DELETED
                ]
            );

        /** @var $purchaseOrder PurchaseOrder */
        $purchaseOrder = $queryBuilder->getQuery()->getResult();

        if (count($purchaseOrder) != 1) {
            return null;
        }

        return $purchaseOrder[0];
    }

    /**
     * PurchaseOrder is in progress, when:
     *  + there is an "purchase_order_item_reception" without any assigned "purchase_order_items"
     * OR
     *  + there is any "receiving" items within users "purchase_order_item_reception"
     *
     * @param $userId
     *
     * @return int|null
     */
    public function getUserInProgressPurchaseOrderId($userId)
    {
        $queryBuilder = $this->createQueryBuilder('purchaseOrder')
            ->select('purchaseOrder.id')
            ->leftJoin(
                'NatueStockBundle:PurchaseOrderItemReception',
                'purchaseOrderItemReception',
                'WITH',
                'purchaseOrder.id = purchaseOrderItemReception.purchaseOrder'
            )
            ->leftJoin(
                'NatueStockBundle:PurchaseOrderItem',
                'purchaseOrderItem',
                'WITH',
                'purchaseOrderItem.purchaseOrderItemReception = purchaseOrderItemReception.id'
            )
            ->where('purchaseOrderItemReception.user = :userId')
            ->andWhere('purchaseOrderItem.status = :status OR purchaseOrderItem.id IS NULL')
            ->andWhere('purchaseOrder.volumesReceived < purchaseOrder.volumesTotal')
            ->setMaxResults(1)
            ->setParameters(
                [
                    'userId' => $userId,
                    'status' => EnumPurchaseOrderItemStatusType::STATUS_RECEIVING,
                ]
            );

        $result = $queryBuilder->getQuery()->getArrayResult();

        if (count($result)) {
            return $result[0]['id'];
        }

        return null;
    }

    /**
     * @param PurchaseOrder              $purchaseOrder
     * @param PurchaseOrderItemReception $purchaseOrderItemReception
     */
    public function increasePurchaseOrderVolumesReceived(
        PurchaseOrder $purchaseOrder,
        PurchaseOrderItemReception $purchaseOrderItemReception
    ) {
        $volumesReceived = $purchaseOrder->getVolumesReceived() + $purchaseOrderItemReception->getVolumes();

        $purchaseOrder->setVolumesReceived($volumesReceived);
        $this->getEntityManager()->persist($purchaseOrder);
        $this->getEntityManager()->flush();
    }

    /**
     * @param int $purchaseOrderId
     * @return array
     */
    public function findWithSupplier($purchaseOrderId)
    {
        $query = $this->createQueryBuilder('purchaseOrder')
            ->leftJoin('purchaseOrder.zedSupplier', 'zedSupplier')
            ->where('purchaseOrder.id = :id')
            ->setParameters(
                [
                    'id' => $purchaseOrderId
                ]
            )
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createQueryBuilderForOrdersGrid()
    {
        $queryBuilder = $this->createQueryBuilder('purchaseOrder')
            ->addSelect("
                CASE WHEN COUNT(purchaseOrderItem.id) = SUM(
                    CASE WHEN purchaseOrderItem.status IN (:statuses)
                    THEN 1
                    ELSE 0
                    END
                )
                THEN 'Received'
                ELSE 'Pending'
                END
                as purchaseStatus
            ")
            ->addSelect('purchaseOrder.id AS purchaseOrderId')
            ->addSelect('zedSupplier.name AS zedSupplierName')
            ->leftJoin(
                'NatueStockBundle:PurchaseOrderItem',
                'purchaseOrderItem',
                'WITH',
                'purchaseOrderItem.purchaseOrder = purchaseOrder.id'
            )
            ->leftJoin('purchaseOrder.zedSupplier', 'zedSupplier')
            ->setParameter(
                'statuses',
                [
                    EnumPurchaseOrderItemStatusType::STATUS_INCOMING,
                    EnumPurchaseOrderItemStatusType::STATUS_RECEIVING,
                ],
                \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
            )
            ->groupBy('purchaseOrder.id')
            ->orderBy('purchaseOrder.createdAt', 'DESC');

        return $queryBuilder;
    }
}
