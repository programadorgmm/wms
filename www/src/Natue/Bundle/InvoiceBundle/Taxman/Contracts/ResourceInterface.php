<?php

namespace Natue\Bundle\InvoiceBundle\Taxman\Contracts;

/**
 * Interface ResourceInterface
 * @package Natue\Bundle\InvoiceBundle\Taxman\Contracts
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
interface ResourceInterface
{
    /**
     * @return string
     */
    public function getResourceName();
}
