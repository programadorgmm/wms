<?php

namespace Natue\Bundle\InvoiceBundle\Taxes\Exceptions;

/**
 * Class PisCofinsNotFoundException
 * @package Natue\Bundle\InvoiceBundle\Taxes\Exceptions
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class PisCofinsNotFoundException extends \InvalidArgumentException
{
    /**
     * @var string
     */
    protected $message = 'The value from ZedProduct.pisCofins could not be matched to any cst/aliquot.';
}
