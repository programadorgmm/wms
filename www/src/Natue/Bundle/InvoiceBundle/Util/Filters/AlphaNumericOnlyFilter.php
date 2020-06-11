<?php

namespace Natue\Bundle\InvoiceBundle\Util\Filters;

use Natue\Bundle\InvoiceBundle\Util\Contracts\Filter;

/**
 * Class AlphaNumericOnlyFilter
 * @package Natue\Bundle\InvoiceBundle\Util\Filters
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class AlphaNumericOnlyFilter implements Filter
{
    /**
     * @param string $input
     * @return string
     */
    public function filter($input)
    {
        return preg_replace('/[^a-zA-Z0-9\s]/', '', $input);
    }
}
