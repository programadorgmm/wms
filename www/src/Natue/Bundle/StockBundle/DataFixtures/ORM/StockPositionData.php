<?php

namespace Natue\Bundle\StockBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Natue\Bundle\StockBundle\DataFixtures\Constants;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\StockBundle\Entity\StockItem;
use Natue\Bundle\StockBundle\Entity\StockPosition as StockPositionEntity;

class StockPositionData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadPosition($manager);
        $this->loadPositionItem($manager);
        $this->loadPositionWithInventoryStarted($manager);

        $this->loadPositionWithOneItemAndQuantity2($manager);
    }

    /**
     * This class has to be defined in order class
     *
     * @return int|bool
     */
    public function getOrder()
    {
        return \Natue\Bundle\CoreBundle\DataFixtures\Order::getOrder($this);
    }

    /**
     * return @void
     */
    private function loadPosition(ObjectManager $manager)
    {
        $stockPosition = (new StockPositionEntity())
            ->setName(Constants::POSITION_ITEM_SINGLE)
            ->setSort(0)
            ->setPickable(0)
            ->setInventory(0)
            ->setEnabled(1)
            ->setCreatedAt(new \DateTime())
            ->setUser($this->getReference('user-test'));

        $this->setReference('stock-position', $stockPosition);

        $manager->persist($stockPosition);
        $manager->flush();
    }

    /**
     * return @void
     */
    private function loadPositionItem(ObjectManager $manager)
    {
        $stockItem = (new StockItem())
            ->setStatus(EnumStockItemStatusType::STATUS_READY)
            ->setZedProduct($this->getReference('zed-product'))
            ->setDateExpiration(new \DateTime('next Sunday'))
            ->setBarcode('12345')
            ->setStockPosition($this->getReference('stock-position'))
            ->setZedOrderItem($this->getReference('zed-order-item'))
            ->setPurchaseOrderItem($this->getReference('purchase-order-item'))
            ->setUser($this->getReference('user-test'));

        $manager->persist($stockItem);
        $manager->flush();
    }

    /**
     * return @void
     */
    private function loadPositionWithInventoryStarted(ObjectManager $manager)
    {
        $stockPosition = (new StockPositionEntity())
            ->setName(Constants::POSITION_INVENTORIZED)
            ->setSort(0)
            ->setPickable(0)
            ->setInventory(1)
            ->setEnabled(1)
            ->setCreatedAt(new \DateTime())
            ->setUser($this->getReference('user-test2'));

        $manager->persist($stockPosition);
        $manager->flush();
    }

    /**
     * return @void
     */
    private function loadPositionWithOneItemAndQuantity2(ObjectManager $manager)
    {
        $itemQuantity = 2;

        $stockPosition = (new StockPositionEntity())
            ->setName(Constants::POSITION_ITEM_SINGLE_QUANTITY_2)
            ->setSort(0)
            ->setPickable(0)
            ->setInventory(0)
            ->setEnabled(1)
            ->setCreatedAt(new \DateTime())
            ->setUser($this->getReference('user-test'));

        $manager->persist($stockPosition);
        $manager->flush();

        for ($i = 0; $i < $itemQuantity; $i++) {
            $stockItem = (new StockItem())
                ->setStatus(EnumStockItemStatusType::STATUS_READY)
                ->setZedProduct($this->getReference('zed-product'))
                ->setDateExpiration(new \DateTime('next Sunday'))
                ->setBarcode('item1Quantity2')
                ->setStockPosition($stockPosition)
                ->setZedOrderItem($this->getReference('zed-order-item'))
                ->setPurchaseOrderItem($this->getReference('purchase-order-item'))
                ->setUser($this->getReference('user-test'));

            $manager->persist($stockItem);
            $manager->flush();
        }
    }
}
