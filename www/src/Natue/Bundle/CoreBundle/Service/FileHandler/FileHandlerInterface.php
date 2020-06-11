<?php

namespace Natue\Bundle\CoreBundle\Service\FileHandler;

/**
 * Interface FileHandler
 */
interface FileHandlerInterface
{
    const PERMISSION_PUBLIC  = 'public';
    const PERMISSION_PRIVATE = 'private';

    /**
     * @param string $fileName
     * @param string $file File location on tmp
     * @param string $permission
     *
     * @return string Place to get the file
     */
    public function save($fileName, $file, $permission);

    /**
     * @param string $file File name with path
     *
     * @return bool
     */
    public function exists($file);

    /**
     * @param string $file File name with path
     *
     * @return string Path on disk
     */
    public function read($file);

    /**
     * @param string $file File name with path
     *
     * @return mixed
     */
    public function delete($file);
}
