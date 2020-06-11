<?php

namespace Natue\Bundle\StockBundle\Tests\Repository;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\StockBundle\Repository\StockItemRepository;

/**
 * StockItemRepository test
 */
class StockItemRepositoryTest extends WebTestCase
{
    /** @var StockItemRepository $repo */
    private $repository;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->repository = self::$entityManager
            ->getRepository('NatueStockBundle:StockItem');
    }

    /**
     * @return void
     */
    public function testFindForUpdateAction()
    {
        $stockItem       = $this->stockItemFactory();
        $stockPosition   = $this->stockPositionFactory();
        $barcode         = uniqid();
        $zedProduct      = $this->zedProductFactory();

        $stockItem->setStockPosition($stockPosition);
        $stockItem->setBarcode($barcode);
        $stockItem->setZedProduct($zedProduct);

        self::$entityManager->persist($stockItem);
        self::$entityManager->flush();

        self::$entityManager->beginTransaction();
        $items = $this->repository->findForUpdateAction(
            $zedProduct,
            $stockPosition,
            EnumStockItemStatusType::STATUS_INCOMING,
            $stockItem->getDateExpiration(),
            $barcode
        );
        self::$entityManager->getConnection()->commit();

        $this->assertEquals($items[0], $stockItem);
    }
}
