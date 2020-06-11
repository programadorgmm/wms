<?php

namespace Natue\Bundle\StockBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\GridAbstract;

/**
 * Class AverageCostGrid
 * @package Natue\Bundle\StockBundle\Grid
 */
class AverageCostGrid extends GridAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setupGrid()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->container->get('translator');

        $this->addColumn($translator->trans('SKU'))
            ->setField('sku')
            ->setIndex('zedProduct.sku')
            ->getFilter()
            ->getOperator()
            ->setComparisonType('equal');

        $this->addColumn($translator->trans('Product Name'))
            ->setField('name')
            ->setIndex('zedProduct.name')
            ->getFilter()
            ->getOperator()
            ->setComparisonType('equal');

        $this->addColumn($translator->trans('Cost Average'))
            ->setField('costAverage')
            ->setIndex('costAverage')
            ->setFilterType('number_range')
            ->getFilter()
            ->setOperatorType('having_number_range');
    }
}
