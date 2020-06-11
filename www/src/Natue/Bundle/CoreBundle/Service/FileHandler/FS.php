<?php

namespace Natue\Bundle\CoreBundle\Service\FileHandler;

/**
 * FS file handler
 */
class FS extends FileHandlerAbstract implements FileHandlerInterface
{
    /**
     * @var string
     */
    private $savePath;

    /**
     * @param string $savePath
     */
    public function __construct($savePath)
    {
        $this->savePath = $savePath;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($file)
    {
        return file_exists($this->savePath . $file);
    }

    /**
     * {@inheritdoc}
     */
    public function save($fileName, $file, $permission = FileHandlerInterface::PERMISSION_PRIVATE)
    {
        $newFile = $this->cleanupPath($this->savePath . $fileName);

        $this->createFolderForPath($newFile);

        if (!@rename($file, $newFile)) {
            throw new \Exception('Failed to save file');
        }

        return $newFile;
    }

    /**
     * {@inheritdoc}
     */
    public function read($file)
    {
        return $this->cleanupPath($this->savePath . $file);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($file)
    {
        return @unlink($this->savePath . $file);
    }
}
