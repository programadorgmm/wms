<?php

namespace Natue\Bundle\StockBundle\CST\ICMS;

use Natue\Bundle\StockBundle\CST\AbstractFactory;

/**
 * Class Factory
 * @package Natue\Bundle\StockBundle\IcmsCst
 */
class Factory extends AbstractFactory
{
    /**
     * @return array
     */
    protected function getTypes()
    {
        return [
            'st' => [
                'name' => 'ST',
                'cst_list' => [10, 30, 60, 70, 201, 202, 203, 500]
            ]
        ];
    }

    /**
     * @param int $cst
     *
     * @return CST
     */
    public function create($cst)
    {
        $type = $this->filterTypeByCST((int) $cst);

        if (empty($type)) {
            return new CST($cst, 'ICMS Alíquota');
        }

        return new CST($cst, $type['name']);
    }
}