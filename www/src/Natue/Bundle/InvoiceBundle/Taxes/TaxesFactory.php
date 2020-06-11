<?php

namespace Natue\Bundle\InvoiceBundle\Taxes;

use Natue\Bundle\InvoiceBundle\Taxes\Calculators\CofinsCalculator;
use Natue\Bundle\InvoiceBundle\Taxes\Calculators\IcmsCalculator;
use Natue\Bundle\InvoiceBundle\Taxes\Calculators\PisCalculator;
use Natue\Bundle\StockBundle\Entity\StockItem;

/**
 * Class TaxesFactory
 * @package Natue\Bundle\InvoiceBundle\Taxes
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class TaxesFactory
{
    /**
     * @param \Natue\Bundle\StockBundle\Entity\StockItem $item
     * @param int                                        $quantity
     * @return \Natue\Bundle\InvoiceBundle\Taxes\Taxes
     */
    public function create(StockItem $item, $quantity)
    {
        $taxes = new Taxes();
        $taxes->setPis((new PisCalculator($item, $quantity))->getCalculatedTax());
        $taxes->setIcms((new IcmsCalculator($item, $quantity))->getCalculatedTax());
        $taxes->setCofins((new CofinsCalculator($item, $quantity))->getCalculatedTax());

        return $taxes;
    }
}
