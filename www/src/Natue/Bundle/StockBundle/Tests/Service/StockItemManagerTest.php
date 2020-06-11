<?php
namespace Natue\Bundle\StockBundle\Tests\StateMachine;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * StockItemManagerTest service test
 */
class StockItemManagerTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $sellItemsWithExpirationGreaterThan = '+11 days';
        $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $token->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue('testUser'));

        $security = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->disableOriginalConstructor()
            ->getMock();

        $security->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $stockItemStateMachine = $this->getMockBuilder('Natue\Bundle\StockBundle\StateMachine\StockItem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->getMockBuilder('Natue\Bundle\StockBundle\Service\StockItemManager')
            ->setConstructorArgs([$doctrine, $security, $stockItemStateMachine, $sellItemsWithExpirationGreaterThan])
            ->getMock();
    }

    /**
     * @return void
     */
    public function testChangePosition()
    {
        $stockItemManager = $this->getMockBuilder('Natue\Bundle\StockBundle\Service\StockItemManager')
            ->disableOriginalConstructor()
            ->setMethods(['validatePosition'])
            ->getMock();

        $stockItemManager->expects($this->once())
            ->method('validatePosition')
            ->will($this->returnValue(true));

        $stockItem = $this->getMockBuilder('Natue\Bundle\StockBundle\Entity\StockItem')
            ->disableOriginalConstructor()
            ->getMock();

        $stockPosition = $this->getMockBuilder('Natue\Bundle\StockBundle\Entity\StockPosition')
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $this->setVariable($stockItemManager, 'entityManager', $entityManager);

        $stockItemManager->changePosition($stockItem, $stockPosition);
    }

    /**
     * @return void
     */
    public function testValidatePosition()
    {
        $this->helperValidatePositionRepoReturnValueAndExpectedException(false, false);
    }

    /**
     * @return void
     */
    public function testValidatePositionThrowException()
    {
        $this->helperValidatePositionRepoReturnValueAndExpectedException(true, true);
    }


    /**
     * @param bool $repoReturnValue
     * @param bool $isExceptionExpected
     */
    private function helperValidatePositionRepoReturnValueAndExpectedException(
        $repoReturnValue,
        $isExceptionExpected
    ) {
        $stockItemRepository = $this->getMockBuilder('Natue\Bundle\StockBundle\Repository\StockItemRepository')
            ->disableOriginalConstructor()
            ->setMethods(['findFirstByZedProductAndPositionWithDifferentDateExpirationAndBarcode'])
            ->getMock();

        $stockItemRepository->expects($this->once())
            ->method('findFirstByZedProductAndPositionWithDifferentDateExpirationAndBarcode')
            ->will($this->returnValue($repoReturnValue));

        $stockItem = $this->getMockBuilder('Natue\Bundle\StockBundle\Entity\StockItem')
            ->disableOriginalConstructor()
            ->setMethods(['getZedProduct', 'getDateExpiration', 'getBarcode'])
            ->getMock();

        $zedProduct = $this->getMockBuilder('Natue\Bundle\ZedBundle\Entity\ZedProduct')
            ->disableOriginalConstructor()
            ->getMock();

        $stockItem->expects($this->once())
            ->method('getZedProduct')
            ->will($this->returnValue($zedProduct));

        $stockItem->expects($this->once())
            ->method('getDateExpiration')
            ->will($this->returnValue(new \DateTime()));

        $stockPosition = $this->getMockBuilder('Natue\Bundle\StockBundle\Entity\StockPosition')
            ->disableOriginalConstructor()
            ->getMock();

        $stockItemManager = $this->getMockBuilder('Natue\Bundle\StockBundle\Service\StockItemManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->setVariable($stockItemManager, 'stockItemRepository', $stockItemRepository);

        if ($isExceptionExpected) {
            $this->setExpectedException('Exception');
        }

        $this->invokeMethod($stockItemManager, 'validatePosition', [$stockItem, $stockPosition]);
    }
}
