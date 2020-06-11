<?php

namespace Natue\Bundle\InvoiceBundle\Taxes\Entities;

/**
 * Class Tax
 * @package Natue\Bundle\InvoiceBundle\Taxes\Entities
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
abstract class AbstractTax
{
    /**
     * @var string
     */
    protected $cst;

    /**
     * @var float
     */
    protected $aliquot;

    /**
     * @var float
     */
    protected $baseValue;

    /**
     * @var float
     */
    protected $value;
    
    /**
     * @return string
     */
    public function getCst()
    {
        return $this->cst;
    }

    /**
     * @param string $cst
     */
    public function setCst($cst)
    {
        $this->cst = $cst;
    }

    /**
     * @return float
     */
    public function getAliquot()
    {
        return $this->aliquot;
    }

    /**
     * @param float $aliquot
     */
    public function setAliquot($aliquot)
    {
        $this->aliquot = $aliquot;
    }

    /**
     * @return float
     */
    public function getBaseValue()
    {
        return $this->baseValue;
    }

    /**
     * @param float $baseValue
     */
    public function setBaseValue($baseValue)
    {
        $this->baseValue = $baseValue;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
