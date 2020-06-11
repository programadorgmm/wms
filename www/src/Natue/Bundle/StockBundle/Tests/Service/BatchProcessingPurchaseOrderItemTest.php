<?php

namespace Natue\Bundle\StockBundle\Tests\Service;

use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType;
use Symfony\Component\Validator\Validator;
use Natue\Bundle\StockBundle\Service\BatchProcessingPurchaseOrderItem;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItem;
use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * BulkInsertPurchaseOrderItem service test
 */
class BatchProcessingPurchaseOrderItemTest extends WebTestCase
{
    /**
     * Test the constructor
     *
     * @return void
     */
    public function testConstructor()
    {

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $batchProcessingPurchaseOrderItemTest = $this->getMockBuilder(
            'Natue\Bundle\StockBundle\Service\BatchProcessingPurchaseOrderItem'
        )
            ->setConstructorArgs([$entityManager])
            ->getMock();


        $this->assertAttributeEquals($entityManager, 'entityManager', $batchProcessingPurchaseOrderItemTest);
    }

    /**
     * Test the method bulkInsert
     *
     * @return void
     */
    public function testBulkInsert()
    {
        $mockPurchaseOrderItem = $this->getMockBuilder('Natue\Bundle\StockBundle\Entity\PurchaseOrderItem')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getCost',
                    'getPurchaseOrder',
                    'getStatus',
                    'getZedProduct'
                ]
            )
            ->getMock();

        $initialStatus = 'incoming';

        $mockClonedPurchaseOrderItem = $this->getMockBuilder('Natue\Bundle\StockBundle\Entity\PurchaseOrderItem')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setPurchaseOrderItemStatus',
                ]
            )
            ->getMock();
        $mockClonedPurchaseOrderItem->expects($this->any())
            ->method('setPurchaseOrderItemStatus')
            ->will($this->returnValue(1));

        $mockPurchaseOrder = $this->getMockBuilder('Natue\Bundle\StockBundle\Entity\PurchaseOrder')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPurchaseOrder->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $mockZedProduct = $this->getMockBuilder('Natue\Bundle\ZedBundle\Entity\ZedProduct')
            ->disableOriginalConstructor()
            ->getMock();
        $mockZedProduct->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $mockPurchaseOrderItem->expects($this->any())
            ->method('getCost')
            ->will($this->returnValue(1));
        $mockPurchaseOrderItem->expects($this->any())
            ->method('getPurchaseOrder')
            ->will($this->returnValue($mockPurchaseOrder));
        $mockPurchaseOrderItem->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue($initialStatus));
        $mockPurchaseOrderItem->expects($this->any())
            ->method('getZedProduct')
            ->will($this->returnValue($mockZedProduct));

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getConnection',
                    'prepare',
                    'persist',
                    'execute',
                    'flush',
                    'detach',
                    'clear',
                ]
            )
            ->getMock();

        $batchProcessingPurchaseOrderItem = new BatchProcessingPurchaseOrderItem($entityManager);

        $entityManager->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($entityManager));
        $entityManager->expects($this->any())
            ->method('prepare')
            ->will($this->returnValue($entityManager));
        $entityManager->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(1));
        $entityManager->expects($this->any())
            ->method('flush')
            ->will($this->returnValue(1));
        $entityManager->expects($this->any())
            ->method('detach')
            ->will($this->returnValue(1));
        $entityManager->expects($this->any())
            ->method('clear')
            ->will($this->returnValue(1));

        $batchProcessingPurchaseOrderItem->bulkInsert(1, $mockPurchaseOrderItem);
        $batchProcessingPurchaseOrderItem->bulkInsert(0, $mockPurchaseOrderItem);
    }

    /**
     * Test the method bulkUpdate
     *
     * @return void
     */
    public function testBulkUpdate()
    {
        $oldCost = 1;
        $newCost = 1;

        $mockPurchaseOrderItemStatus = $this->getMockBuilder('Natue\Bundle\StockBundle\Entity\PurchaseOrderItemStatus')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPurchaseOrderItemStatus->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $mockPurchaseOrder = $this->getMockBuilder('Natue\Bundle\StockBundle\Entity\PurchaseOrder')
            ->disableOriginalConstructor()
            ->getMock();
        $mockPurchaseOrder->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $mockZedProduct = $this->getMockBuilder('Natue\Bundle\ZedBundle\Entity\ZedProduct')
            ->disableOriginalConstructor()
            ->getMock();
        $mockZedProduct->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $query         = $this->getMockBuilder('\Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setParameters',
                    'setLockMode',
                    'execute'
                ]
            )
            ->getMockForAbstractClass();
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'createQuery',
                    'setParameters',
                    'execute'
                ]
            )
            ->getMock();

        $entityManager->expects($this->once())
            ->method('createQuery')
            ->will($this->returnValue($query));

        $query->expects($this->once())
            ->method('setParameters')
            ->will($this->returnValue(1));
        $query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(3));

        $batchProcessing = new BatchProcessingPurchaseOrderItem($entityManager);
        $batchProcessing->bulkUpdate(
            $mockPurchaseOrder,
            $oldCost,
            $mockZedProduct,
            $newCost,
            0,
            0,
            0,
            $mockPurchaseOrderItemStatus
        );
    }

    /**
     * Test the method buildPurchaseOrderItem
     *
     * @return void
     */
    public function testBuildPurchaseOrderItem()
    {

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $batchProcessingPurchaseOrderItem = $this->getMockBuilder(
            'Natue\Bundle\StockBundle\Service\BatchProcessingPurchaseOrderItem'
        )
            ->setConstructorArgs([$entityManager])
            ->getMock();

        $cost          = 10;
        $initialStatus = EnumPurchaseOrderItemStatusType::STATUS_INCOMING;

        $mockPurchaseOrder = $this->getMockBuilder('Natue\Bundle\StockBundle\Entity\PurchaseOrder')
            ->disableOriginalConstructor()
            ->getMock();

        $mockZedProduct = $this->getMockBuilder('Natue\Bundle\ZedBundle\Entity\ZedProduct')
            ->disableOriginalConstructor()
            ->getMock();

        /* @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = $this->invokeMethod(
            $batchProcessingPurchaseOrderItem,
            'buildPurchaseOrderItem',
            [$cost, $mockPurchaseOrder, $mockZedProduct]
        );

        $this->assertEquals(get_class($purchaseOrderItem), 'Natue\Bundle\StockBundle\Entity\PurchaseOrderItem');
        $this->assertEquals($purchaseOrderItem->getCost(), $cost);
        $this->assertEquals($purchaseOrderItem->getStatus(), $initialStatus);
        $this->assertEquals($purchaseOrderItem->getPurchaseOrder(), $mockPurchaseOrder);
        $this->assertEquals($purchaseOrderItem->getZedProduct(), $mockZedProduct);
    }
}
