<?php

namespace Natue\Bundle\ZedBundle\Tests\Event;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\ZedBundle\Service\DbSynchronizer;

/**
 * DbSynchronizer test
 */
class DbSynchronizerTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $wmsConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $zedConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $dbSynchronizer = new DbSynchronizer($wmsConnection, $zedConnection);

        $this->assertAttributeEquals($wmsConnection, 'wmsConnection', $dbSynchronizer);
        $this->assertAttributeEquals($zedConnection, 'zedConnection', $dbSynchronizer);
    }

    /**
     * @return void
     */
    public function testSynchronize()
    {
        $tables             = ['test1'];
        $timeDiff           = new \DateTime();
        $arrayLatestChanges = [0 => ['id' => 'test2']];

        $expectedResults    = null;

        $dbSynchronizer = $this->getMockBuilder('Natue\Bundle\ZedBundle\Service\DbSynchronizer')
            ->disableOriginalConstructor()
            ->setMethods(['getWmsViewName', 'getZedTableName', 'getLatestChanges', 'insertOrUpdateOnDuplicateKey'])
            ->getMock();

        $dbSynchronizer->expects($this->once())
            ->method('getWmsViewName')
            ->with($tables[0])
            ->will($this->returnValue('wmsViewName'));
        $dbSynchronizer->expects($this->once())
            ->method('getZedTableName')
            ->with($tables[0])
            ->will($this->returnValue('zedViewName'));
        $dbSynchronizer->expects($this->once())
            ->method('getLatestChanges')
            ->with('wmsViewName', $timeDiff)
            ->will($this->returnValue($arrayLatestChanges));
        $dbSynchronizer->expects($this->once())
            ->method('insertOrUpdateOnDuplicateKey')
            ->with('zedViewName', $arrayLatestChanges[0])
            ->will($this->returnValue(true));

        $this->assertEquals($expectedResults, $dbSynchronizer->synchronize($tables, $timeDiff));
    }

    /**
     * @return void
     */
    public function testGetWmsViewName()
    {
        $tableName = 'test1';

        $wmsConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $zedConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $dbSynchronizer = new DbSynchronizer($wmsConnection, $zedConnection);

        $this->assertEquals('wms_' . $tableName, $this->invokeMethod($dbSynchronizer, 'getWmsViewName', [$tableName]));
    }

    /**
     * @return void
     */
    public function testGetZedTableName()
    {
        $tableName = 'test1';

        $wmsConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $zedConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $dbSynchronizer = new DbSynchronizer($wmsConnection, $zedConnection);

        $this->assertEquals('zed_' . $tableName, $this->invokeMethod($dbSynchronizer, 'getZedTableName', [$tableName]));
    }

    /**
     * @return void
     */
    public function testInsertOrUpdateOnDuplicateKey()
    {
        $tableName = 'test1';
        $data      = ['potato' => 'salad'];

        $wmsConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'executeQuery',
                ]
            )
            ->getMock();

        $wmsConnection->expects($this->once())
            ->method('executeQuery')
            ->will($this->returnValue(true));

        $zedConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $dbSynchronizer = new DbSynchronizer($wmsConnection, $zedConnection);

        $expectedResult = null;
        $result         = $this->invokeMethod($dbSynchronizer, 'insertOrUpdateOnDuplicateKey', [$tableName, $data]);

        $this->assertEquals($expectedResult, $result);
    }
}
