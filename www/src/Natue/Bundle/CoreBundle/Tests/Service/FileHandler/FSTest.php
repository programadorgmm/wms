<?php

namespace Natue\Bundle\CoreBundle\Tests\Service\FileHandler;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\CoreBundle\Service\FileHandler\FS;

/**
 * FS Test
 */
class FSTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testExists()
    {
        $id = uniqid();

        $service = new FS('/tmp/');

        $this->assertEquals(false, $service->exists($id));

        file_put_contents('/tmp/' . $id, 'content');

        $this->assertEquals(true, $service->exists($id));

        unlink('/tmp/' . $id);
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $id         = uniqid();
        $saveFolder = '/tmp/' . $id . '/';
        $fileToSave = '/tmp/' . $id . 'source-file';
        $newFile    = $id . 'new-file';

        $service = new FS($saveFolder);

        file_put_contents($fileToSave, 'content');

        $this->assertEquals(
            $saveFolder . $newFile,
            $service->save(
                $newFile,
                $fileToSave
            )
        );

        $this->assertEquals(false, is_file($fileToSave));
        $this->assertEquals(true, is_file($saveFolder . $newFile));

        unlink($saveFolder . $newFile);

        $this->setExpectedException(
            'Exception',
            'Failed to save file'
        );

        $service->save($newFile, $fileToSave);
    }

    /**
     * @return void
     */
    public function testRead()
    {
        $id = uniqid();

        $service = new FS('/tmp/');

        $this->assertEquals('/tmp/' . $id, $service->read($id));
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $id   = uniqid();
        $file = '/tmp/' . $id;

        file_put_contents($file, 'content');

        $service = new FS('/tmp/');
        $service->delete($id);

        $this->assertEquals(false, is_file($file));
    }
}
