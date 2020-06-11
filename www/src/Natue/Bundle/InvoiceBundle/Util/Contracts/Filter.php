<?php

namespace Natue\Bundle\InvoiceBundle\Util\Contracts;

/**
 * Interface Filter
 * @package Natue\Bundle\InvoiceBundle\Util\Contracts
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
interface Filter
{
    /**
     * @param mixed $input
     * @return mixed
     */
    public function filter($input);
}
