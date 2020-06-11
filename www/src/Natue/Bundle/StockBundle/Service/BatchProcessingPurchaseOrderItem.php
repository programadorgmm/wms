<?php

namespace Natue\Bundle\StockBundle\Service;

use Doctrine\ORM\EntityManager;

use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItem;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;

/**
 * Class BatchProcessingPurchaseOrderItem
 * See: http://docs.doctrine-project.org/en/2.0.x/reference/batch-processing.html
 */
class BatchProcessingPurchaseOrderItem
{
    const BATCH_SIZE = 25000;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     *
     * @return BatchProcessingPurchaseOrderItem
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Bulk inserts in Doctrine are best performed in batches.
     * Insert the same purchase order item many times. Return the last purchase order item inserted
     *
     * @param int $numberOfInsertionsToDo
     * @param PurchaseOrderItem $originalPurchaseOrderItem
     * @param string|null $initialStatus
     *
     * @throws \Doctrine\DBAL\DBALException
     * @return void
     */
    public function bulkInsert(
        $numberOfInsertionsToDo,
        PurchaseOrderItem $originalPurchaseOrderItem,
        $initialStatus = null
    ) {
        $purchaseOrderItem = clone $originalPurchaseOrderItem;

        if ($initialStatus) {
            $purchaseOrderItem->setStatus($initialStatus);
        }

        for ($i = 0; $i < $numberOfInsertionsToDo; $i++) {
            $this->entityManager->persist(clone $purchaseOrderItem);
        }

        $this->entityManager->flush();
    }

    /**
     * Update the cost of many items
     *
     * @param PurchaseOrder $purchaseOrder
     * @param integer $oldCost
     * @param ZedProduct $zedProduct
     * @param integer $newCost
     * @param string $status
     *
     * @return void
     */
    public function bulkUpdate(
        PurchaseOrder $purchaseOrder,
        $oldCost,
        ZedProduct $zedProduct,
        $newCost,
        $icmsSt,
        $icms,
        $invoiceCost,
        $status
    ) {
        $query = $this->entityManager->createQuery(
            'UPDATE Natue\Bundle\StockBundle\Entity\PurchaseOrderItem purchaseOrderItem
             SET purchaseOrderItem.cost = :newCost , purchaseOrderItem.icmsSt = :icmsSt, purchaseOrderItem.icms = :icms, purchaseOrderItem.invoiceCost = :invoiceCost
             WHERE purchaseOrderItem.zedProduct = :zedProductId
             AND purchaseOrderItem.cost = :oldCost
             AND purchaseOrderItem.purchaseOrder = :purchaseOrderId
             AND purchaseOrderItem.status = :status'
        );

        $query->setParameters(
            [
                'zedProductId' => $zedProduct->getId(),
                'oldCost' => $oldCost,
                'purchaseOrderId' => $purchaseOrder->getId(),
                'newCost' => $newCost,
                'icmsSt' => $icmsSt,
                'status' => $status,
                'icms' => $icms,
                'invoiceCost' => $invoiceCost,
            ]
        );


        $query->execute();
    }

    /**
     * Delete many items (this function is like bulkDeleteAll but with a limit)
     *
     * @param PurchaseOrder $purchaseOrder
     * @param integer $cost
     * @param ZedProduct $zedProduct
     * @param string $status
     * @param integer|null $limit
     *
     * @return array
     */
    public function bulkDelete(
        PurchaseOrder $purchaseOrder,
        $cost,
        ZedProduct $zedProduct,
        $status,
        $limit = null
    ) {
        $sql = 'UPDATE purchase_order_item
             SET status = :deletedStatus
             WHERE zed_product = :zedProductId
             AND cost = :cost
             AND purchase_order = :purchaseOrderId
             AND status = :status';

        if ($limit) {
            $sql .= ' LIMIT ' . $limit;
        }

        $params = [
            'deletedStatus' => EnumPurchaseOrderItemStatusType::STATUS_DELETED,
            'zedProductId' => $zedProduct->getId(),
            'cost' => $cost,
            'purchaseOrderId' => $purchaseOrder->getId(),
            'status' => $status
        ];

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Build an entity purchaseOrderItem
     *
     * @param integer $cost
     * @param PurchaseOrder $purchaseOrder
     * @param ZedProduct $zedProduct
     *
     * @return PurchaseOrderItem
     */
    protected function buildPurchaseOrderItem(
        $cost,
        PurchaseOrder $purchaseOrder,
        ZedProduct $zedProduct
    ) {
        $purchaseOrderItem = new PurchaseOrderItem();
        $purchaseOrderItem->setCost($cost);
        $purchaseOrderItem->setPurchaseOrder($purchaseOrder);
        $purchaseOrderItem->setZedProduct($zedProduct);

        return $purchaseOrderItem;
    }
}
