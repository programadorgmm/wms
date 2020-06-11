<?php

namespace Natue\Bundle\InvoiceBundle\Taxes\Contracts;

/**
 * Interface CalculatorInterface
 * @package Natue\Bundle\InvoiceBundle\Taxes\Contracts
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
interface CalculatorInterface
{
    /**
     * @return mixed
     */
    public function getCalculatedTax();
}
