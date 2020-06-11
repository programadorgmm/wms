<?php

namespace Natue\Bundle\InvoiceBundle\Taxes\Calculators;

use Natue\Bundle\InvoiceBundle\Taxes\Entities\Cofins;

/**
 * Class CofinsCalculator
 * @package Natue\Bundle\InvoiceBundle\Taxes\Calculators
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class CofinsCalculator extends AbstractPisCofinsCalculator
{
    /**
     * @return \Natue\Bundle\InvoiceBundle\Taxes\Entities\Pis
     */
    protected function getTaxEntity()
    {
        return new Cofins();
    }

    /**
     * @return array
     */
    protected function getTypesAndAliquots()
    {
        return array_replace_recursive(self::DEFAULT_TYPES_AND_ALIQUOTS, [
            'default' => [
                'aliquot' => 7.6
            ],
        ]);
    }
}
