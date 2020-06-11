<?php

namespace Natue\Bundle\StockBundle\Tests\Service;

use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Symfony\Component\Validator\Validator;

use Natue\Bundle\StockBundle\Service\BatchProcessingStockItem;
use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * BatchProcessingStockItemTest service test
 */
class BatchProcessingStockItemTest extends WebTestCase
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
            'Natue\Bundle\StockBundle\Service\BatchProcessingStockItem'
        )
            ->setConstructorArgs([$entityManager])
            ->getMock();

        $this->assertAttributeEquals($entityManager, 'entityManager', $batchProcessingPurchaseOrderItemTest);
    }

    /**
     * Test the method bulkUpdate
     *
     * @return void
     */
    public function testBulkUpdate()
    {
        $zedProduct = $this->getMockBuilder('Natue\Bundle\ZedBundle\Entity\ZedProduct')
            ->disableOriginalConstructor()
            ->getMock();

        $stockPosition = $this->getMockBuilder('Natue\Bundle\StockBundle\Entity\StockPosition')
            ->disableOriginalConstructor()
            ->getMock();

        $initialStatus = EnumStockItemStatusType::STATUS_INCOMING;

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'execute',
                    'setParameters'
                ]
            )
            ->getMockForAbstractClass();

        $query->expects($this->once())
            ->method('execute')
            ->will($this->returnValue(true));
        $query->expects($this->once())
            ->method('setParameters')
            ->will($this->returnValue($query));

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getQuery',
                    'update',
                    'set',
                    'where',
                    'andWhere'
                ]
            )
            ->getMock();

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $queryBuilder->expects($this->once())
            ->method('update')
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->exactly(2))
            ->method('set')
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->once())
            ->method('where')
            ->will($this->returnValue($queryBuilder));
        $queryBuilder->expects($this->exactly(4))
            ->method('andWhere')
            ->will($this->returnValue($queryBuilder));

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'createQueryBuilder'
                ]
            )
            ->getMock();

        $entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));


        $batchProcessing = new BatchProcessingStockItem($entityManager);
        $batchProcessing->bulkUpdate(
            $zedProduct,
            $stockPosition,
            $initialStatus,
            new \DateTime(),
            '123',
            new \DateTime(),
            '321'
        );
    }
}
