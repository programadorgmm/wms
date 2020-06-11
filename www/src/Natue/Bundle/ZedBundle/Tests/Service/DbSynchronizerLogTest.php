<?php

namespace Natue\Bundle\ZedBundle\Tests\Event;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\ZedBundle\Entity\ZedSynchronizationLog;
use Natue\Bundle\ZedBundle\Service\DbSynchronizerLog;

/**
 * DbSynchronizerLog test
 */
class DbSynchronizerLogTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $logRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['getManager', 'getRepository'])
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->with()
            ->will($this->returnValue($entityManager));
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with('NatueZedBundle:ZedSynchronizationLog')
            ->will($this->returnValue($logRepository));

        $dbSynchronizerLog = new DbSynchronizerLog($doctrine);

        $this->assertAttributeEquals($entityManager, 'entityManager', $dbSynchronizerLog);
        $this->assertAttributeEquals($logRepository, 'logRepository', $dbSynchronizerLog);
    }

    /**
     * @return void
     */
    public function testTryReserve()
    {
        $zedSynchronizationLog = $this->getMockBuilder('Natue\Bundle\ZedBundle\Entity\ZedSynchronizationLog')
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $logRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['getManager', 'getRepository'])
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->with()
            ->will($this->returnValue($entityManager));
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with('NatueZedBundle:ZedSynchronizationLog')
            ->will($this->returnValue($logRepository));

        $dbSynchronizerLog = $this->getMockBuilder('Natue\Bundle\ZedBundle\Service\DbSynchronizerLog')
            ->setConstructorArgs([$doctrine])
            ->setMethods(['reserve'])
            ->getMock();
        $dbSynchronizerLog->expects($this->once())
            ->method('reserve')
            ->will($this->returnValue($zedSynchronizationLog));

        $this->assertEquals($zedSynchronizationLog, $dbSynchronizerLog->tryReserve());
    }

    /**
     * @return void
     */
    public function testForceReserve()
    {
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $logRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['getManager', 'getRepository'])
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->with()
            ->will($this->returnValue($entityManager));
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with('NatueZedBundle:ZedSynchronizationLog')
            ->will($this->returnValue($logRepository));

        $zedSynchronizationLog = $this->getMockBuilder('Natue\Bundle\ZedBundle\Entity\ZedSynchronizationLog')
            ->disableOriginalConstructor()
            ->getMock();

        $dbSynchronizerLog = $this->getMockBuilder('Natue\Bundle\ZedBundle\Service\DbSynchronizerLog')
            ->setConstructorArgs([$doctrine])
            ->setMethods(['reserve'])
            ->getMock();
        $dbSynchronizerLog->expects($this->once())
            ->method('reserve')
            ->will($this->returnValue($zedSynchronizationLog));

        $this->assertEquals($zedSynchronizationLog, $dbSynchronizerLog->forceReserve());
    }

    /**
     * @return void
     */
    public function testReserve()
    {
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['persist', 'flush'])
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->willReturn(true);
        $entityManager->expects($this->once())
            ->method('flush')
            ->willReturn(true);

        $logRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['getManager', 'getRepository'])
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->with()
            ->will($this->returnValue($entityManager));
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with('NatueZedBundle:ZedSynchronizationLog')
            ->will($this->returnValue($logRepository));

        $dbSynchronizerLog = $this->getMockBuilder('Natue\Bundle\ZedBundle\Service\DbSynchronizerLog')
            ->setConstructorArgs([$doctrine])
            ->getMock();

        /** @var ZedSynchronizationLog $zedSynchronizationLog */
        $zedSynchronizationLog = $this->invokeMethod($dbSynchronizerLog, 'reserve', []);
        $this->assertTrue($zedSynchronizationLog instanceof ZedSynchronizationLog);
        $this->assertEquals($zedSynchronizationLog->getStatus(), ZedSynchronizationLog::STATUS_RUNNING);
    }

    public function testRelease()
    {
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['persist', 'flush'])
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->willReturn(true);
        $entityManager->expects($this->once())
            ->method('flush')
            ->willReturn(true);

        $logRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['getManager', 'getRepository'])
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->with()
            ->will($this->returnValue($entityManager));
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with('NatueZedBundle:ZedSynchronizationLog')
            ->will($this->returnValue($logRepository));

        $dbSynchronizerLog = new DbSynchronizerLog($doctrine);

        $zedSynchronizationLog = $this->getMockBuilder('Natue\Bundle\ZedBundle\Entity\ZedSynchronizationLog')
            ->disableOriginalConstructor()
            ->setMethods(['setStatus', 'setFinishedAt'])
            ->getMock();
        $zedSynchronizationLog->expects($this->once())
            ->method('setStatus')
            ->with(ZedSynchronizationLog::STATUS_SUCCESS)
            ->willReturn(true);
        $zedSynchronizationLog->expects($this->once())
            ->method('setFinishedAt')
            ->willReturn(true);
        $dbSynchronizerLog->release($zedSynchronizationLog);
    }


    public function testFail()
    {
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['persist', 'flush'])
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->willReturn(true);
        $entityManager->expects($this->once())
            ->method('flush')
            ->willReturn(true);

        $logRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['getManager', 'getRepository'])
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->with()
            ->will($this->returnValue($entityManager));
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with('NatueZedBundle:ZedSynchronizationLog')
            ->will($this->returnValue($logRepository));

        $dbSynchronizerLog = new DbSynchronizerLog($doctrine);

        $zedSynchronizationLog = $this->getMockBuilder('Natue\Bundle\ZedBundle\Entity\ZedSynchronizationLog')
            ->disableOriginalConstructor()
            ->setMethods(['setStatus', 'setFinishedAt'])
            ->getMock();
        $zedSynchronizationLog->expects($this->once())
            ->method('setStatus')
            ->with(ZedSynchronizationLog::STATUS_FAILURE)
            ->willReturn(true);
        $dbSynchronizerLog->fail($zedSynchronizationLog);
    }
}
