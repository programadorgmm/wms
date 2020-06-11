<?php

namespace Natue\Bundle\InvoiceBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class InvoiceNumberRepository
 * @package Natue\Bundle\InvoiceBundle\Repository
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class InvoiceNumberRepository extends EntityRepository
{
    /**
     * @param integer $series
     * @return \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber|null
     */
    public function firstRecyclableNumberBySeries($series)
    {
        return $this->findOneBy([
            'isRecyclable' => true,
            'series' => $series,
        ]);
    }

    /**
     * @param integer $series
     * @return \Natue\Bundle\InvoiceBundle\Entity\InvoiceNumber|null
     */
    public function lastUsedNumberBySeries($series)
    {
        return $this->findOneBy([
            'isRecyclable' => false,
            'series' => $series,
        ], ['id' => 'DESC']);
    }
}
