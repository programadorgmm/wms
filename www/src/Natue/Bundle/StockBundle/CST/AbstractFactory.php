<?php

namespace Natue\Bundle\StockBundle\CST;

/**
 * Class Factory
 * @package Natue\Bundle\StockBundle\CST
 */
abstract class AbstractFactory
{
    /**
     * @param int $cst
     *
     * @return AbstractCST
     */
    abstract public function create($cst);

    /**
     * @return array
     */
    abstract protected function getTypes();

    /**
     * @param int $cst
     *
     * @return array
     */
    protected function filterTypeByCST($cst)
    {
        $filtered = array_filter($this->getTypes(), function (array $type) use ($cst) {
            return in_array($cst, $type['cst_list']);
        });

        if (empty($filtered)) {
            return [];
        }

        return reset($filtered);
    }
}