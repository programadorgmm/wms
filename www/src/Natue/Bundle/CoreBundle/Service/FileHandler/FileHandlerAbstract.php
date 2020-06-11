<?php

namespace Natue\Bundle\CoreBundle\Service\FileHandler;

/**
 * Abstract FileHandler
 */
abstract class FileHandlerAbstract
{
    /**
     * @param string $path
     *
     * @return string mixed
     */
    protected function cleanupPath($path)
    {
        return str_replace(['//', '///', '////'], '', $path);
    }

    /**
     * @param string $path
     */
    protected function createFolderForPath($path)
    {
        $path = $this->cleanupPath($path);

        $stringArray = explode(DIRECTORY_SEPARATOR, $path);
        $lastElement = array_pop($stringArray);
        $createDir   = str_replace($lastElement, '', $path);

        @mkdir($createDir, 0777, true);
    }
}
