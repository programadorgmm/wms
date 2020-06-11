<?php

namespace Natue\Bundle\CoreBundle\Datatable\Column;

use Sg\DatatablesBundle\Datatable\Column\Column;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AggregateColumn
 * @package Natue\Bundle\CoreBundle\Datatable\Column
 */
class AggregateColumn extends Column
{
    /**
     * @var string
     */
    private $aggregateExpr;

    /**
     * @var string
     */
    private $joinableExpr;

    /**
     * @param OptionsResolver $resolver
     * @return $this
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined('aggregate_expr');
        $resolver->setDefined('joinable_expr');
        $resolver->setAllowedTypes('aggregate_expr', 'string');
        $resolver->setAllowedTypes('joinable_expr', 'string');
        $resolver->setRequired('aggregate_expr');

        return $this;
    }

    /**
     * @return string
     */
    public function getAggregateExpr()
    {
        return $this->aggregateExpr;
    }

    /**
     * @param string $aggregateExpr
     *
     * @return $this
     */
    public function setAggregateExpr($aggregateExpr)
    {
        $this->aggregateExpr = $aggregateExpr;

        return $this;
    }

    /**
     * @return string
     */
    public function getJoinableExpr()
    {
        return $this->joinableExpr;
    }

    /**
     * @param string $joinableExpr
     * @return $this
     */
    public function setJoinableExpr($joinableExpr)
    {
        $this->joinableExpr = $joinableExpr;

        return $this;
    }

    /**
     * @return string
     */
    public function getDql()
    {
        return $this->getAggregateExpr() . ' as ' . $this->getData();
    }
}