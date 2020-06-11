<?php

namespace Natue\Bundle\InvoiceBundle\Util\Filters;

use Natue\Bundle\InvoiceBundle\Util\Contracts\Filter;

/**
 * Class PipelineFilter
 * @package Natue\Bundle\InvoiceBundle\Util\Filters
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class PipelineFilter implements Filter
{
    /**
     * @var array|\Natue\Bundle\InvoiceBundle\Util\Contracts\Filter[]
     */
    protected $filters;

    /**
     * @param array|\Natue\Bundle\InvoiceBundle\Util\Contracts\Filter[] $filters
     */
    public function __construct(array $filters)
    {
        foreach ($filters as $filter) {
            $this->pushFilter($filter);
        }
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Util\Contracts\Filter $filter
     * @return void
     */
    public function pushFilter(Filter $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    public function filter($input)
    {
        foreach ($this->filters as $filter) {
            $input = $filter->filter($input);
        }

        return $input;
    }
}
