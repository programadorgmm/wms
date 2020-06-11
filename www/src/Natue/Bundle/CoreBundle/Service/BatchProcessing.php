<?php

namespace Natue\Bundle\CoreBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;

/**
 * Class BatchProcessing
 * @package Natue\Bundle\CoreBundle\Service
 */
class BatchProcessing implements \Iterator
{
    /**
     * Flushing period
     */
    const BATCH_SIZE = 250;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    private $entities = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var int
     */
    private $length = 0;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->entityManager = $doctrine->getManager();
    }

    /**
     * @return int
     */
    public function getBatchSize()
    {
        return self::BATCH_SIZE;
    }

    /**
     * @param $entities
     *
     * @return BatchProcessing
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
        $this->length = count($entities);

        $this->rewind();

        return $this;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->entities[$this->position];
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->entityManager->persist($this->current());

        if (($this->position % self::BATCH_SIZE) == 0) {
            $this->entityManager->flush();
        }

        ++$this->position;

        if ($this->position == $this->length) {
            $this->entityManager->flush();
        }
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->entities[$this->position]);
    }
}
