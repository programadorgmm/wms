<?php

namespace Natue\Bundle\CoreBundle\Service;

use Symfony\Component\HttpKernel\Kernel;

use Natue\Bundle\CoreBundle\Service\FileHandler\FileHandlerInterface;

/**
 * FileHandler
 */
class FileHandler implements FileHandlerInterface
{
    /**
     * @var FileHandlerInterface
     */
    private $service;

    /**
     * @param Kernel $kernel
     * @param string $service
     */
    public function __construct(Kernel $kernel, $service)
    {
        $this->service = $kernel->getContainer()->get($service);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($file)
    {
        return $this->service->exists($file);
    }

    /**
     * {@inheritdoc}
     */
    public function save($fileName, $file, $permission = FileHandlerInterface::PERMISSION_PRIVATE)
    {
        return $this->service->save($fileName, $file, $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function read($file)
    {
        return $this->service->read($file);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($file)
    {
        return $this->service->delete($file);
    }
}
