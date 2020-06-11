<?php

namespace Natue\Bundle\CoreBundle\Tests\Service;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\CoreBundle\Service\FileHandler;

/**
 * FileHandler Test
 */
class FileHandlerTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testCompleteFileHandlerService()
    {
        $testService = $this->getMockBuilder('Natue\Bundle\CoreBundle\Service\FileHandler\FS')
            ->disableOriginalConstructor()
            ->setMethods(['exists', 'save', 'read', 'delete'])
            ->getMock();

        $testService->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true));

        $testService->expects($this->once())
            ->method('save')
            ->will($this->returnValue('saveReturn'));

        $testService->expects($this->once())
            ->method('read')
            ->will($this->returnValue('readReturn'));

        $testService->expects($this->once())
            ->method('delete')
            ->will($this->returnValue(true));

        self::$kernel->getContainer()->set('natue.file.handler.test', $testService);

        $fileHandler = new FileHandler(
            self::$kernel,
            'natue.file.handler.test'
        );

        $this->assertEquals(true, $fileHandler->exists('test'));
        $this->assertEquals('saveReturn', $fileHandler->save('test', 'test'));
        $this->assertEquals('readReturn', $fileHandler->read('test'));
        $this->assertEquals(true, $fileHandler->delete('test'));
    }
}
