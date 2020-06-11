<?php

namespace Natue\Bundle\InvoiceBundle\Services;

use Doctrine\ORM\EntityManager;
use Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber;
use Natue\Bundle\InvoiceBundle\Repository\InvoiceNumberRepository;

/**
 * Class InvoiceNumberManager
 * @package Natue\Bundle\InvoiceBundle\Services
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class InvoiceNumberService
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Natue\Bundle\InvoiceBundle\Repository\InvoiceNumberRepository
     */
    protected $repository;

    /**
     * @param \Doctrine\ORM\EntityManager                                    $entityManager
     * @param \Natue\Bundle\InvoiceBundle\Repository\InvoiceNumberRepository $repository
     */
    public function __construct(EntityManager $entityManager, InvoiceNumberRepository $repository)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $repository;
    }

    /**
     * @param int $series
     * @return \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber
     */
    public function getAvailableNumber($series)
    {
        if ($recyclableNumber = $this->repository->firstRecyclableNumberBySeries($series)) {
            return $this->recycle($recyclableNumber);
        }

        return $this->create($series);
    }

    /**
     * @param int $series
     * @return \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber
     */
    public function create($series)
    {
        $lastNumber = $this->repository->lastUsedNumberBySeries($series);

        $newNumber = new InvoiceNumber();
        $newNumber->setSeries($series);
        $newNumber->setIsRecyclable(false);
        $newNumber->setNumber(
            $lastNumber ? $lastNumber->getNumber() + 1 : 1
        );

        $this->save($newNumber);

        return $newNumber;
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber $invoiceNumber
     * @return \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber
     */
    public function recyclable(InvoiceNumber $invoiceNumber)
    {
        $invoiceNumber->setIsRecyclable(true);

        $this->save($invoiceNumber);

        return $invoiceNumber;
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber $invoiceNumber
     * @return \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber
     */
    protected function recycle(InvoiceNumber $invoiceNumber)
    {
        $invoiceNumber->setIsRecyclable(false);

        $this->save($invoiceNumber);

        return $invoiceNumber;
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber $invoiceNumber
     * @return void
     */
    protected function save(InvoiceNumber $invoiceNumber)
    {
        $this->entityManager->persist($invoiceNumber);
        $this->entityManager->flush($invoiceNumber);
    }
}
