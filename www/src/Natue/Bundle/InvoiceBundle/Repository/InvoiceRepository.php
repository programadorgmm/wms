<?php

namespace Natue\Bundle\InvoiceBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;

/**
 * Class InvoiceRepository
 * @package Natue\Bundle\InvoiceBundle\Repository
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class InvoiceRepository extends EntityRepository
{
    /**
     * @param \Natue\Bundle\StockBundle\Entity\PurchaseOrder $purchaseOrder
     * @return int
     */
    public function countInvoicesByPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        return $this->createQueryBuilder('invoice')
            ->select('COUNT(invoice)')
            ->where('invoice.purchaseOrder = :purchaseOrder')
            ->setParameter('purchaseOrder', $purchaseOrder)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
