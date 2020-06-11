<?php

namespace Natue\Bundle\StockBundle\CST\PIS;

use Natue\Bundle\StockBundle\CST\AbstractFactory;

/**
 * Class Factory
 * @package Natue\Bundle\StockBundle\CST\PIS
 */
class Factory extends AbstractFactory
{
    /**
     * @return array
     */
    protected function getTypes()
    {
        return [
            'aliquota' => [
                'name' => 'PIS Alíquota',
                'cst_list' => [1]
            ],
            'monofasico' => [
                'name' => 'Monofásico',
                'cst_list' => [4]
            ],
            'aliquota-zero' => [
                'name' => 'Alíquota Zero',
                'cst_list' => [5, 6, 7]
            ],
            'books' => [
                'name' => 'Books',
                'cst_list' => [8]
            ],
            'suspensao' => [
                'name' => 'Suspensão',
                'cst_list' => [9]
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
            return new CST($cst, 'PIS Outros');
        }

        return new CST($cst, $type['name']);
    }
}