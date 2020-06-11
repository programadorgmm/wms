<?php

namespace Natue\Bundle\CoreBundle\Tests;

/**
 * Factory test - Test each factory method from WebTestCase
 */
class FactoryTest extends WebTestCase
{
    /**
     * Test groupFactory method from WebTestCase
     *
     * @return void
     */
    public function testGroupFactory()
    {
        $this->assertEquals(get_class($this->groupFactory()), 'Natue\Bundle\UserBundle\Entity\Group');
    }

    /**
     * Test userFactory method from WebTestCase
     *
     * @return void
     */
    public function testUserFactory()
    {
        $this->assertEquals(get_class($this->userFactory()), 'Natue\Bundle\UserBundle\Entity\User');
    }

    /**
     * Test stockPositionFactory method from WebTestCase
     *
     * @return void
     */
    public function testStockPositionFactory()
    {
        $this->assertEquals(get_class($this->stockPositionFactory()), 'Natue\Bundle\StockBundle\Entity\StockPosition');
    }

    /**
     * Test stockItemFactory method from WebTestCase
     *
     * @return void
     */
    public function testStockItemFactory()
    {
        $this->assertEquals(get_class($this->stockItemFactory()), 'Natue\Bundle\StockBundle\Entity\StockItem');
    }

    /**
     * Test purchaseOrderFactory method from WebTestCase
     *
     * @return void
     */
    public function testPurchaseOrderFactory()
    {
        $this->assertEquals(get_class($this->purchaseOrderFactory()), 'Natue\Bundle\StockBundle\Entity\PurchaseOrder');
    }

    /**
     * Test purchaseOrderItemFactory method from WebTestCase
     *
     * @return void
     */
    public function testPurchaseOrderItemFactory()
    {
        $this->assertEquals(
            get_class($this->purchaseOrderItemFactory()),
            'Natue\Bundle\StockBundle\Entity\PurchaseOrderItem'
        );
    }

    /**
     * Test zedProductFactory method from WebTestCase
     *
     * @return void
     */
    public function testZedProductFactory()
    {
        $this->assertEquals(get_class($this->zedProductFactory()), 'Natue\Bundle\ZedBundle\Entity\ZedProduct');
    }

    /**
     * Test zedSupplierFactory method from WebTestCase
     *
     * @return void
     */
    public function testZedSupplierFactory()
    {
        $this->assertEquals(get_class($this->zedSupplierFactory()), 'Natue\Bundle\ZedBundle\Entity\ZedSupplier');
    }

    /**
     * Test zedOrderFactory method from WebTestCase
     *
     * @return void
     */
    public function testZedOrderFactory()
    {
        $this->assertEquals(
            get_class($this->zedOrderFactory()),
            'Natue\Bundle\ZedBundle\Entity\ZedOrder'
        );
    }

    /**
     * Test zedOrderItemFactory method from WebTestCase
     *
     * @return void
     */
    public function testZedOrderItemFactory()
    {
        $this->assertEquals(
            get_class($this->zedOrderItemFactory()),
            'Natue\Bundle\ZedBundle\Entity\ZedOrderItem'
        );
    }

    /**
     * Test zedOrderItemStatusFactory method from WebTestCase
     *
     * @return void
     */
    public function testZedOrderItemStatusFactory()
    {
        $this->assertEquals(
            get_class($this->zedOrderItemStatusFactory()),
            'Natue\Bundle\ZedBundle\Entity\ZedOrderItemStatus'
        );
    }
}
