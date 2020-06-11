<?php

namespace Natue\Bundle\StockBundle\Grid\Datatable;

use Sg\DatatablesBundle\Datatable\View;

/**
 * Class PurchaseOrderGrid
 * @package Natue\Bundle\StockBundle\Grid\Datatable
 */
class PurchaseOrderGrid extends View\AbstractDatatableView
{
    /**
     * @param array $options
     */
    public function buildDatatable(array $options = [])
    {
        return ;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return \Natue\Bundle\StockBundle\Entity\PurchaseOrder::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        return function($line) {
            if (array_key_exists(0, $line)) {
                $line[0]['Status'] = $line['Status'];

                return $line[0];
            }

            return $line;
        };
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'purchase_order_grid';
    }
}