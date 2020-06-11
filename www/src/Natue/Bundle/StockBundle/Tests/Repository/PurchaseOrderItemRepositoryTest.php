<?php

namespace Natue\Bundle\StockBundle\Tests\Repository;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\StockBundle\Repository\PurchaseOrderItemRepository;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType;

/**
 * PurchaseOrderItemRepository test
 */
class PurchaseOrderItemRepositoryTest extends WebTestCase
{
    /** @var PurchaseOrderItemRepository $repo */
    private $repository;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->repository = self::$entityManager
            ->getRepository('NatueStockBundle:PurchaseOrderItem');
    }

    /**
     * @return void
     */
    public function testFindOneByZedProductAndCostAndPurchaseOrder()
    {
        $zedProduct        = $this->zedProductFactory();
        $cost              = 1;
        $purchaseOrder     = $this->purchaseOrderFactory();
        $purchaseOrderItem = $this->purchaseOrderItemFactory();

        $purchaseOrderItem->setZedProduct($zedProduct);
        $purchaseOrderItem->setPurchaseOrder($purchaseOrder);
        $purchaseOrderItem->setCost($cost);

        self::$entityManager->persist($purchaseOrderItem);
        self::$entityManager->flush($purchaseOrderItem);

        $this->assertEquals(
            $this->repository->findOneByZedProductAndCostAndPurchaseOrder($zedProduct, $cost, $purchaseOrder),
            $purchaseOrderItem
        );
    }

    /**
     * @return void
     */
    public function testFindByZedProductAndCostAndPurchaseOrderAndStatus()
    {
        $zedProduct        = $this->zedProductFactory();
        $cost              = 1;
        $purchaseOrder     = $this->purchaseOrderFactory();
        $purchaseOrderItem = $this->purchaseOrderItemFactory();
        $initialStatus     = EnumPurchaseOrderItemStatusType::STATUS_INCOMING;

        $purchaseOrderItem->setZedProduct($zedProduct);
        $purchaseOrderItem->setPurchaseOrder($purchaseOrder);
        $purchaseOrderItem->setCost($cost);
        self::$entityManager->persist($purchaseOrderItem);
        self::$entityManager->flush($purchaseOrderItem);

        // Without lock
        $this->assertEquals(
            $this->repository->findByZedProductAndCostAndPurchaseOrderAndStatus(
                $zedProduct,
                $cost,
                $purchaseOrder,
                $initialStatus
            )[0],
            $purchaseOrderItem
        );

        // With lock
        self::$entityManager->beginTransaction();
        $this->assertEquals(
            $this->repository->findByZedProductAndCostAndPurchaseOrderAndStatus(
                $zedProduct,
                $cost,
                $purchaseOrder,
                $initialStatus,
                true
            )[0],
            $purchaseOrderItem
        );
        self::$entityManager->getConnection()->commit();
    }
}
