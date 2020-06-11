<?php

namespace Natue\Bundle\InvoiceBundle\Taxes\Calculators;

use Natue\Bundle\InvoiceBundle\Taxes\Contracts\CalculatorInterface;
use Natue\Bundle\InvoiceBundle\Taxes\Entities\AbstractTax;
use Natue\Bundle\InvoiceBundle\Taxes\Entities\Pis;
use Natue\Bundle\InvoiceBundle\Taxes\Exceptions\PisCofinsNotFoundException;
use Natue\Bundle\StockBundle\Entity\StockItem;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;

/**
 * Class PisCalculator
 * @package Natue\Bundle\InvoiceBundle\Taxes\Calculators
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class PisCalculator extends AbstractPisCofinsCalculator
{
    /**
     * @return \Natue\Bundle\InvoiceBundle\Taxes\Entities\Pis
     */
    protected function getTaxEntity()
    {
        return new Pis();
    }

    /**
     * @return array
     */
    protected function getTypesAndAliquots()
    {
        return array_replace_recursive(self::DEFAULT_TYPES_AND_ALIQUOTS, [
            'default' => [
                'aliquot' => 1.65
            ],
        ]);
    }
}
