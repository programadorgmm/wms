<?php

namespace Natue\Bundle\ZedBundle\Tests\Command;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * DbSynchronizeCommand test
 */
class DbSynchronizeCommandTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testConfigure()
    {
        $dbSynchronizeCommand = $this->getMockBuilder('Natue\Bundle\ZedBundle\Command\DbSynchronizeCommand')
            ->disableOriginalConstructor()
            ->setMethods(['setName', 'setDescription', 'addOption'])
            ->getMock();

        $dbSynchronizeCommand
            ->expects($this->once())
            ->method('setName')
            ->with('natue:zed:db-sync')
            ->will($this->returnValue(1));

        $dbSynchronizeCommand
            ->expects($this->once())
            ->method('setDescription')
            ->with('Synchronize database: ZED views into WMS tables')
            ->will($this->returnValue(1));

        $dbSynchronizeCommand
            ->expects($this->once())
            ->method('addOption')
            ->will($this->returnValue(1));

        $this->invokeMethod($dbSynchronizeCommand, 'configure', []);
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $zedSynchronizationLog = $this->getMockBuilder('Natue\Bundle\ZedBundle\Entity\ZedSynchronizationLog')
            ->disableOriginalConstructor()
            ->getMock();

        $inputInterface = $this->getMockBuilder('Symfony\Component\Console\Input\InputInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $inputInterface->expects($this->once())
            ->method('getOption')
            ->with('force')
            ->willReturn(true);

        $outputInterface = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $outputInterface->expects($this->once())
            ->method('writeln')
            ->with('Synchronization success.')
            ->willReturn(true);

        $dbSynchronizeCommand = $this->getMockBuilder('Natue\Bundle\ZedBundle\Command\DbSynchronizeCommand')
            ->disableOriginalConstructor()
            ->setMethods(['getContainer'])
            ->getMock();

        $container = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $zedSynchronizerLogService = $this->getMockBuilder('Natue\Bundle\ZedBundle\Service\DbSynchronizerLog')
            ->disableOriginalConstructor()
            ->setMethods(['forceReserve', 'getFromDateTime', 'release'])
            ->getMock();
        $zedSynchronizerLogService->expects($this->once())
            ->method('forceReserve')
            ->willReturn($zedSynchronizationLog);
        $zedSynchronizerLogService->expects($this->once())
            ->method('getFromDateTime')
            ->willReturn(1);
        $zedSynchronizerLogService->expects($this->once())
            ->method('release')
            ->willReturn(true);

        $doctrineDbalDefaultConnectionService = $this->getMockBuilder(
            'Doctrine\Bundle\DoctrineBundle\ConnectionFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods(['beginTransaction', 'commit'])
            ->getMock();
        $doctrineDbalDefaultConnectionService->expects($this->once())
            ->method('beginTransaction');
        $doctrineDbalDefaultConnectionService->expects($this->once())
            ->method('commit');

        $zedSynchronizerService = $this->getMockBuilder('Natue\Bundle\ZedBundle\Service\DbSynchronizer')
            ->disableOriginalConstructor()
            ->getMock();
        $zedSynchronizerService->expects($this->once())
            ->method('synchronize')
            ->willReturn([]);

        $dbSynchronizeCommand->expects($this->exactly(3))
            ->method('getContainer')
            ->willReturn($container);

        $container->expects($this->at(0))
            ->method('get')
            ->with('natue.zed.synchronizer_log')
            ->willReturn($zedSynchronizerLogService);
        $container->expects($this->at(1))
            ->method('get')
            ->with('doctrine.dbal.default_connection')
            ->willReturn($doctrineDbalDefaultConnectionService);
        $container->expects($this->at(2))
            ->method('get')
            ->with('natue.zed.synchronizer')
            ->willReturn($zedSynchronizerService);

        $this->invokeMethod($dbSynchronizeCommand, 'execute', [$inputInterface, $outputInterface]);
    }
}
