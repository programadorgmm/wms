<?php

namespace Natue\Bundle\StockBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItem;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType as Status;

class PurchaseOrderData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadPurchaseOrder($manager);
        $this->loadPurchaseOrderItem($manager);
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
     * @param ObjectManager $manager
     */
    private function loadPurchaseOrder(ObjectManager $manager)
    {
        $purchaseOrder = (new PurchaseOrder())
            ->setUser($this->getReference('user-test'));

        $this->addReference('purchase-order', $purchaseOrder);

        $manager->persist($purchaseOrder);
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function loadPurchaseOrderItem(ObjectManager $manager)
    {
        $purchaseOrderItem = (new PurchaseOrderItem())
            ->setCreatedAt(new \DateTime())
            ->setPurchaseOrder($this->getReference('purchase-order'))
            ->setStatus(Status::STATUS_RECEIVED)
            ->setZedProduct($this->getReference('zed-product'));

        $this->addReference('purchase-order-item', $purchaseOrderItem);

        $manager->persist($purchaseOrderItem);
        $manager->flush();
    }
}
