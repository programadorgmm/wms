<?php

namespace Natue\Bundle\ZedBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Natue\Bundle\ZedBundle\Entity\ZedOrderItem;
use Natue\Bundle\ZedBundle\Entity\ZedOrderItemStatus;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;
use Natue\Bundle\ZedBundle\Entity\ZedSupplier;

class ZedProductData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->loadZedSupplier($manager);
        $this->loadZedProduct($manager);
        $this->loadZedOrderAndZedOrderItem($manager);
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
    private function loadZedSupplier(ObjectManager $manager)
    {
        $zedSupplier = (new ZedSupplier())
            ->setId(1)
            ->setType(0)
            ->setName('Hilê')
            ->setCnpj('00.509.032/0001-17')
            ->setPhone('(49) 3433-0100')
            ->setAddress1('Caixa Postal,134 BR 282,- Km 511- Xanxerê - SC ')
            ->setZipcode('89820000')
            ->setCreatedAt(new \DateTime());

        $this->addReference('zed-supplier', $zedSupplier);

        $manager->persist($zedSupplier);
        $manager->flush();
    }
    
    /**
     * @param ObjectManager $manager
     */
    private function loadZedProduct(ObjectManager $manager)
    {
        $zedProduct = (new ZedProduct())
            ->setId(1)
            ->setSku('testSku1')
            ->setName('testProduct1')
            ->setBrand('testBrand')
            ->setStatus('approved')
            ->setAttributeSet(1)
            ->setGrossWeight(1)
            ->setZedSupplier($this->getReference('zed-supplier'))
            ->setIsBook(0)
            ->setCreatedAt(new \DateTime());

        $this->addReference('zed-product', $zedProduct);

        $manager->persist($zedProduct);
        $manager->flush();
    }
    
    /**
     * @param ObjectManager $manager
     */
    private function loadZedOrderAndZedOrderItem(ObjectManager $manager)
    {
        $zedOrder = (new ZedOrder())
            ->setId(1)
            ->setIncrementId('DEVBR201450066634691')
            ->setCustomerFirstname('FirstName')
            ->setCustomerLastname('LastName')
            ->setCustomerCpf('775.234.439-74')
            ->setCustomerZipcode('13500313')
            ->setCustomerQuarter('Saúde')
            ->setCustomerState('SP')
            ->setCustomerCity('Rio Claro')
            ->setPriceShipping(773)
            ->setCreatedAt(new \DateTime());

        $this->addReference('zed-order', $zedOrder);

        $zedOrderStatus = (new ZedOrderItemStatus())
            ->setId(1)
            ->setName('StatusName')
            ->setCreatedAt(new \DateTime());

        $zedOrderItem = (new ZedOrderItem())
            ->setId(1)
            ->setZedProduct($this->getReference('zed-product'))
            ->setZedOrderItemStatus($zedOrderStatus)
            ->setZedOrder($zedOrder)
            ->setCreatedAt(new \DateTime());

        $this->addReference('zed-order-item', $zedOrderItem);

        $manager->persist($zedOrderItem);
        $manager->flush();
    }
}
