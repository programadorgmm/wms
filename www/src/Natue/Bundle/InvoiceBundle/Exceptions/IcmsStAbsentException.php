<?php

namespace Natue\Bundle\InvoiceBundle\Exceptions;

use Natue\Bundle\StockBundle\Entity\PurchaseOrderItem;

/**
 * Class IcmsStAbsentException
 * @package Natue\Bundle\InvoiceBundle\Exceptions
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class IcmsStAbsentException extends \InvalidArgumentException
{
    /**
     * @param \Natue\Bundle\StockBundle\Entity\PurchaseOrderItem $purchaseOrderItem
     */
    public function __construct(PurchaseOrderItem $purchaseOrderItem)
    {
        parent::__construct(sprintf(
            'Expected PurchaseOrderItem#%s to have property "icmsST" greater than 0.',
            $purchaseOrderItem->getId()
        ));
    }
}
