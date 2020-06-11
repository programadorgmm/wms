<?php

namespace Natue\Bundle\InvoiceBundle\Exceptions;

/**
 * Class MultiplePurchaseOrderOnInvoiceException
 * @package Natue\Bundle\InvoiceBundle\Exceptions
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class MultiplePurchaseOrderOnInvoiceException extends \InvalidArgumentException
{
    /**
     * @var string
     */
    protected $message = 'Each invoice should contain stock item from only one purchase order.';
}
