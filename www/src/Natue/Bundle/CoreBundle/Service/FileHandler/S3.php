<?php

namespace Natue\Bundle\CoreBundle\Service\FileHandler;

use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl;

/**
 * S3 file handler
 */
class S3 extends FileHandlerAbstract implements FileHandlerInterface
{
    /**
     * @var S3Client
     */
    private $handler;

    /**
     * @var string
     */
    private $bucket;

    /**
     * @var string
     */
    private $savePath;

    /**
     * @param S3Client $s3Client
     * @param string   $bucket
     * @param string   $savePath
     */
    public function __construct(S3Client $s3Client, $bucket, $savePath)
    {
        $this->bucket   = $bucket;
        $this->savePath = $savePath;
        $this->handler  = $s3Client;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($file)
    {
        $result = $this->handler->headObject(
            [
                'Bucket' => $this->bucket,
                'Key'    => $file,
            ]
        );

        return ($result['ContentLength'] > 0);
    }

    /**
     * {@inheritdoc}
     */
    public function save($fileName, $file, $permission = FileHandlerInterface::PERMISSION_PRIVATE)
    {
        switch ($permission) {
            case FileHandlerInterface::PERMISSION_PUBLIC:
                $permission = CannedAcl::PUBLIC_READ;
                break;
            case FileHandlerInterface::PERMISSION_PRIVATE:
                $permission = CannedAcl::PRIVATE_ACCESS;
                break;
        }

        $result = $this->handler->putObject(
            [
                'Bucket'     => $this->bucket,
                'Key'        => $fileName,
                'SourceFile' => $file,
                'ACL'        => $permission
            ]
        );

        @unlink($file);

        return $result['ObjectURL'];
    }

    /**
     * {@inheritdoc}
     */
    public function read($file)
    {
        $newFile = $this->cleanupPath($this->savePath . $file);

        $this->createFolderForPath($newFile);

        $this->handler->getObject(
            [
                'Bucket' => $this->bucket,
                'Key'    => $file,
                'SaveAs' => $newFile
            ]
        );

        return $newFile;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($file)
    {
        $this->handler->deleteObject(
            [
                'Bucket' => $this->bucket,
                'Key'    => $file,
            ]
        );

        return true;
    }
}
