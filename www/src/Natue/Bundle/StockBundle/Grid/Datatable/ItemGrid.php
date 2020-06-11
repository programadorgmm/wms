<?php

namespace Natue\Bundle\StockBundle\Grid\Datatable;

use Sg\DatatablesBundle\Datatable\View;

/**
 * Class ItemGrid
 * @package Natue\Bundle\StockBundle\Grid\Datatable
 */
class ItemGrid extends View\AbstractDatatableView
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
        return \Natue\Bundle\StockBundle\Entity\StockItem::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLineFormatter()
    {
        return function($line) {
            if (array_key_exists(0, $line)) {
                $line[0]['qtd'] = $line['qtd'];

                $dateTimeExpiration = $line[0]['dateExpiration'];
                if ($dateTimeExpiration instanceof \DateTime) {
                    $dateTimeExpiration->add(new \DateInterval('PT4H'));
                    $line[0]['dateExpiration'] = $dateTimeExpiration;
                }

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
        return 'dt_item_grid';
    }
}