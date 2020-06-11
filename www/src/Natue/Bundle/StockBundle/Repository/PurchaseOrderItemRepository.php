<?php

namespace Natue\Bundle\StockBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\DBAL\LockMode;

use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType;
use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItem;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItemReception;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;

/**
 * Repository for PurchaseOrderItem
 */
class PurchaseOrderItemRepository extends EntityRepository
{
    /**
     * Define an array of default options for queries.
     *
     * @return array
     */
    private function getDefaultOptions()
    {
        return [
            'withLock' => true
        ];
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     *
     * @return QueryBuilder
     */
    public function getQueryForListAction(PurchaseOrder $purchaseOrder)
    {
        return $this->createQueryBuilder('purchaseOrderItem')
            ->addSelect('COUNT(zedProduct.sku) AS qty')
            ->addSelect('zedProduct.sku AS productSku')
            ->addSelect('zedProduct.name AS productName')
            ->addSelect('purchaseOrderItem.status as status')
            ->andWhere('purchaseOrderItem.purchaseOrder = :purchaseOrderEntity')
            ->andWhere('purchaseOrderItem.status <> :deletedStatus')
            ->innerJoin('purchaseOrderItem.zedProduct', 'zedProduct')
            ->groupBy('zedProduct.sku')
            ->addGroupBy('purchaseOrderItem.cost')
            ->addGroupBy('purchaseOrderItem.status')
            ->orderBy('zedProduct.name')
            ->setParameters(
                [
                    'purchaseOrderEntity' => $purchaseOrder,
                    'deletedStatus'       => EnumPurchaseOrderItemStatusType::STATUS_DELETED
                ]
            );
    }

    /**
     * @param ZedProduct    $zedProduct
     * @param integer       $cost
     * @param PurchaseOrder $purchaseOrder
     *
     * @return PurchaseOrderItem
     */
    public function findOneByZedProductAndCostAndPurchaseOrder(
        ZedProduct $zedProduct,
        $cost,
        PurchaseOrder $purchaseOrder
    ) {
        $purchaseOrderItem = $this->findOneBy(
            [
                'zedProduct'    => $zedProduct,
                'cost'          => $cost,
                'purchaseOrder' => $purchaseOrder,
            ]
        );

        return $purchaseOrderItem;
    }


    /**
     * @param ZedProduct    $zedProduct
     * @param PurchaseOrder $purchaseOrder
     *
     * @return PurchaseOrderItem
     */
    public function findByZedProductAndPurchaseOrder(
        ZedProduct $zedProduct,
        PurchaseOrder $purchaseOrder
    ) {
        $purchaseOrderItem = $this->findBy(
            [
                'zedProduct'    => $zedProduct,
                'purchaseOrder' => $purchaseOrder,
            ]
        );

        return $purchaseOrderItem;
    }

    /**
     * @param ZedProduct    $zedProduct
     * @param integer       $cost
     * @param PurchaseOrder $purchaseOrder
     * @param string        $status
     *
     * @return int
     */
    public function countByZedProductAndCostAndPurchaseOrderAndStatus(
        ZedProduct $zedProduct,
        $cost,
        PurchaseOrder $purchaseOrder,
        $status
    ) {
        $query = $this->createQueryBuilder('purchaseOrderItem')
            ->select('count(purchaseOrderItem)')
            ->where('purchaseOrderItem.purchaseOrder = :purchaseOrder')
            ->andWhere('purchaseOrderItem.zedProduct    = :zedProduct')
            ->andWhere('purchaseOrderItem.cost          = :cost')
            ->andWhere('purchaseOrderItem.status        = :status')
            ->setParameters(
                [
                    'purchaseOrder' => $purchaseOrder,
                    'zedProduct'    => $zedProduct,
                    'cost'          => $cost,
                    'status'        => $status
                ]
            )
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param ZedProduct    $zedProduct
     * @param integer       $cost
     * @param PurchaseOrder $purchaseOrder
     * @param string        $status
     *
     * @return int
     */
    public function countByZedProductAndCostAndPurchaseOrder(
        ZedProduct $zedProduct,
        $cost,
        PurchaseOrder $purchaseOrder
    ) {
        $query = $this->createQueryBuilder('purchaseOrderItem')
            ->select('count(purchaseOrderItem)')
            ->where('purchaseOrderItem.purchaseOrder = :purchaseOrder')
            ->andWhere('purchaseOrderItem.zedProduct    = :zedProduct')
            ->andWhere('purchaseOrderItem.cost          = :cost')
            ->setParameters(
                [
                    'purchaseOrder' => $purchaseOrder,
                    'zedProduct'    => $zedProduct,
                    'cost'          => $cost
                ]
            )
            ->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * @param ZedProduct    $zedProduct
     * @param integer       $cost
     * @param PurchaseOrder $purchaseOrder
     * @param string        $status
     * @param boolean       $withLock
     *
     * @return array
     */
    public function findByZedProductAndCostAndPurchaseOrderAndStatus(
        ZedProduct $zedProduct,
        $cost,
        PurchaseOrder $purchaseOrder,
        $status,
        $withLock = false
    ) {
        if ($withLock) {
            $queryPurchaseOrderItemListWithInitialStatus = $this->getEntityManager()->createQuery(
                'SELECT purchaseOrderItem FROM Natue\Bundle\StockBundle\Entity\PurchaseOrderItem purchaseOrderItem
                 WHERE purchaseOrderItem.zedProduct = :zedProductId
                 AND purchaseOrderItem.cost = :cost
                 AND purchaseOrderItem.purchaseOrder = :purchaseOrderId
                 AND purchaseOrderItem.status = :status'
            );
            $queryPurchaseOrderItemListWithInitialStatus->setParameters(
                [
                    'purchaseOrderId' => $purchaseOrder->getId(),
                    'zedProductId'    => $zedProduct->getId(),
                    'cost'            => $cost,
                    'status'          => $status
                ]
            );
            $queryPurchaseOrderItemListWithInitialStatus->setLockMode(LockMode::PESSIMISTIC_WRITE);
            $purchaseOrderItemList = $queryPurchaseOrderItemListWithInitialStatus->getResult();

        } else {
            $purchaseOrderItemList = $this->findBy(
                [
                    'purchaseOrder' => $purchaseOrder,
                    'zedProduct'    => $zedProduct,
                    'cost'          => $cost,
                    'status'        => $status
                ]
            );

        }

        return $purchaseOrderItemList;
    }

    /**
     * @param ZedProduct    $zedProduct
     * @param integer       $cost
     * @param PurchaseOrder $purchaseOrder
     *
     * @return PurchaseOrderItem
     */
    public function findByZedProductAndCostAndPurchaseOrder(
        ZedProduct $zedProduct,
        $cost,
        PurchaseOrder $purchaseOrder
    ) {
        $purchaseOrderItem = $this->findBy(
            [
                'zedProduct'    => $zedProduct,
                'cost'          => $cost,
                'purchaseOrder' => $purchaseOrder,
            ]
        );
        return $purchaseOrderItem;
    }

    /**
     * Find Purchase Order Item by barcode
     *
     * @param PurchaseOrder     $purchaseOrder
     * @param string            $barcodeNumber
     * @param array             $options
     *
     * @return array
     */
    public function findIncomingItemsByBarcode(
        PurchaseOrder $purchaseOrder,
        $barcodeNumber,
        array $options = []
    ) {
        $options = array_merge($this->getDefaultOptions(), $options);

        $queryBuilder = $this->createQueryBuilder('purchaseOrderItem')
            ->innerJoin('purchaseOrderItem.zedProduct', 'zedProduct')
            ->innerJoin(
                'NatueZedBundle:ZedProductBarcode',
                'zedProductBarcode',
                'WITH',
                'zedProductBarcode.zedProduct = zedProduct.id'
            )
            ->where('purchaseOrderItem.purchaseOrder = :purchaseOrder')
            ->andWhere('purchaseOrderItem.status = :status')
            ->andWhere('zedProductBarcode.barcode = :barcodeNumber')
            ->setParameters(
                [
                    'purchaseOrder' => $purchaseOrder,
                    'barcodeNumber' => $barcodeNumber,
                    'status'        => EnumPurchaseOrderItemStatusType::STATUS_INCOMING,
                ]
            );

        if (isset($options['limit'])) {
            $queryBuilder->setMaxResults($options['limit']);
        }

        $query = $queryBuilder->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getResult();
    }

    /**
     * Find "receiving" Items by PurchaseOrder and User
     *
     * @param PurchaseOrder $purchaseOrder
     * @param User          $user
     * @param array         $options
     *
     * @return array
     */
    public function findReceivingItemsByPurchaseOrderAndUser(
        PurchaseOrder $purchaseOrder,
        User $user,
        array $options = []
    ) {

        $options = array_merge($this->getDefaultOptions(), $options);

        $query = $this->createQueryBuilder('purchaseOrderItem')
            ->innerJoin(
                'NatueStockBundle:PurchaseOrderItemReception',
                'purchaseOrderItemReception',
                'WITH',
                'purchaseOrderItemReception.id = purchaseOrderItem.purchaseOrderItemReception'
            )
            ->where('purchaseOrderItem.purchaseOrder = :purchaseOrder')
            ->andWhere('purchaseOrderItemReception.user = :user')
            ->andWhere('purchaseOrderItem.status = :status')
            ->setParameters(
                [
                    'purchaseOrder' => $purchaseOrder,
                    'user'          => $user,
                    'status'        => EnumPurchaseOrderItemStatusType::STATUS_RECEIVING,
                ]
            )
            ->getQuery();

        if ($options['withLock']) {
            $query->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getResult();
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     *
     * @return array
     */
    public function getOrderedProductsWithQuantity(PurchaseOrder $purchaseOrder)
    {
        $query = $this->createQueryBuilder('purchaseOrderItem')
            ->select('zedProduct.id AS zedProductId')
            ->addSelect('zedProduct.sku')
            ->addSelect('zedProduct.name AS productName')
            ->addSelect('COUNT(purchaseOrderItem.id) AS totalRequestedQuantity')
            ->leftJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'zedProduct.id = purchaseOrderItem.zedProduct'
            )
            ->where('purchaseOrderItem.purchaseOrder = :purchaseOrder')
            ->andWhere('purchaseOrderItem.status <> :deleted')
            ->groupBy('zedProduct.id')
            ->setParameters(
                [
                    'purchaseOrder' => $purchaseOrder,
                    'deleted'       => EnumPurchaseOrderItemStatusType::STATUS_DELETED
                ]
            )
            ->getQuery();

        return $query->getArrayResult();
    }

    /**
     * @param array $purchaseOrderItemIds
     *
     * @return mixed
     */
    public function unsignPurchaseOrderItemsFromReception(array $purchaseOrderItemIds)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->update('NatueStockBundle:PurchaseOrderItem', 'purchaseOrderItem')
            ->set('purchaseOrderItem.purchaseOrderItemReception', ':null')
            ->where('purchaseOrderItem.id IN (:purchaseOrderItemIds)')
            ->setParameters(
                [
                    'purchaseOrderItemIds' => $purchaseOrderItemIds,
                    'null'                 => null,
                ]
            )
            ->getQuery();

        return $query->execute();
    }

    /**
     * @param PurchaseOrderItemReception $purchaseOrderReception
     *
     * @return array
     */
    public function getProductsQtyAndCostByReception(PurchaseOrderItemReception $purchaseOrderReception)
    {
        $query = $this->createQueryBuilder('purchaseOrderItem')
            ->select('IDENTITY(purchaseOrderItem.zedProduct) AS zed_product')
            ->addSelect('purchaseOrderItem.cost AS cost')
            ->addSelect('COUNT(purchaseOrderItem.id) AS qty')
            ->where('purchaseOrderItem.purchaseOrderItemReception = :reception')
            ->groupBy('purchaseOrderItem.zedProduct')
            ->addGroupBy('purchaseOrderItem.cost')
            ->setParameter('reception', $purchaseOrderReception)
            ->getQuery();

        return $query->getArrayResult();
    }
}
