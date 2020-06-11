<?php

namespace Natue\Bundle\InvoiceBundle\Taxman\Contracts;

/**
 * Interface ArrayableInterface
 * @package Natue\Bundle\InvoiceBundle\Taxman\Contracts
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
interface ArrayableInterface
{
    /**
     * @return array
     */
    public function toArray();
}
