<?php

namespace Natue\Bundle\CoreBundle\Tests\Service\FileHandler;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\CoreBundle\Service\FileHandler\S3;
use Natue\Bundle\CoreBundle\Service\FileHandler\FileHandlerInterface;

/**
 * S3 Test
 */
class S3Test extends WebTestCase
{
    /**
     * @return void
     */
    public function testExists()
    {
        $mockS3 = $this->getMockBuilder('Aws\S3\S3Client')
            ->disableOriginalConstructor()
            ->setMethods(['headObject'])
            ->getMock();

        $mockS3->expects($this->once())
            ->method('headObject')
            ->will($this->returnValue(['ContentLength' => 1]));

        $service = new S3($mockS3, 'test', '/tmp/test/');

        $this->assertEquals(true, $service->exists('test'));

        $mockS3 = $this->getMockBuilder('Aws\S3\S3Client')
            ->disableOriginalConstructor()
            ->setMethods(['headObject'])
            ->getMock();

        $mockS3->expects($this->once())
            ->method('headObject')
            ->will($this->returnValue(['ContentLength' => 0]));

        $service = new S3($mockS3, 'test', '/tmp/test/');

        $this->assertEquals(false, $service->exists('test'));
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $mockS3 = $this->getMockBuilder('Aws\S3\S3Client')
            ->disableOriginalConstructor()
            ->setMethods(['putObject'])
            ->getMock();

        $mockS3->expects($this->exactly(2))
            ->method('putObject')
            ->will($this->returnValue(['ObjectURL' => 'http://test']));

        $id         = uniqid();
        $fileToSave = '/tmp/' . $id;

        file_put_contents($fileToSave, 'content');

        $service = new S3($mockS3, 'test', '/tmp/test/');

        $this->assertEquals('http://test', $service->save('test', $fileToSave));
        $this->assertEquals(false, is_file($fileToSave));

        $id         = uniqid();
        $fileToSave = '/tmp/' . $id;

        file_put_contents($fileToSave, 'content');

        $this->assertEquals(
            'http://test',
            $service->save('test', $fileToSave, FileHandlerInterface::PERMISSION_PUBLIC)
        );
        $this->assertEquals(false, is_file($fileToSave));
    }

    /**
     * @return void
     */
    public function testRead()
    {
        $mockS3 = $this->getMockBuilder('Aws\S3\S3Client')
            ->disableOriginalConstructor()
            ->setMethods(['getObject'])
            ->getMock();

        $mockS3->expects($this->once())
            ->method('getObject')
            ->will($this->returnValue('test'));

        $service = new S3($mockS3, 'test', '/tmp/test/');

        $this->assertEquals('/tmp/test/test', $service->read('test'));
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $mockS3 = $this->getMockBuilder('Aws\S3\S3Client')
            ->disableOriginalConstructor()
            ->setMethods(['deleteObject'])
            ->getMock();

        $mockS3->expects($this->once())
            ->method('deleteObject')
            ->will($this->returnValue(true));

        $service = new S3($mockS3, 'test', '/tmp/test/');

        $this->assertEquals(true, $service->delete('test'));
    }
}
