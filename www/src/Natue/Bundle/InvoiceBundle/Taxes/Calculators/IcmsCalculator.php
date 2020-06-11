<?php

namespace Natue\Bundle\InvoiceBundle\Taxes\Calculators;

use Natue\Bundle\InvoiceBundle\Taxes\Contracts\CalculatorInterface;
use Natue\Bundle\InvoiceBundle\Taxes\Entities\Icms;
use Natue\Bundle\StockBundle\Entity\StockItem;

/**
 * Class IcmsCalculator
 * @package Natue\Bundle\InvoiceBundle\Taxes\Calculators
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class IcmsCalculator implements CalculatorInterface
{
    const VALUE = 0;
    const CST = '90';
    const CST_ST = '60';
    const ORIGIN = 0;
    const MODALITY = 3;
    const ALIQUOT = 0;
    const BASE_VALUE = 0;
    const ALIQUOT_FOR_DONATION = 0;
    const ALIQUOT_FOR_DESTINATION = 0;

    /**
     * @var \Natue\Bundle\StockBundle\Entity\StockItem
     */
    protected $stockItem;

    /**
     * @var int
     */
    protected $quantity;

    /**
     * @param \Natue\Bundle\StockBundle\Entity\StockItem $stockItem
     * @param int $quantity
     */
    public function __construct(StockItem $stockItem, $quantity)
    {
        $this->stockItem = $stockItem;
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getCalculatedTax()
    {
        $icms = new Icms();
        $icms->setOrigin(self::ORIGIN);
        $icms->setCst($this->getCalculatedIcmsCst());
        $icms->setModality(self::MODALITY);
        $icms->setAliquot(self::ALIQUOT);
        $icms->setBaseValue(self::BASE_VALUE);
        $icms->setValue(self::VALUE);
        $icms->setAliquotForDonation(self::ALIQUOT_FOR_DONATION);
        $icms->setAliquotForDestination(self::ALIQUOT_FOR_DESTINATION);

        return $icms;
    }

    /**
     * @return \Natue\Bundle\StockBundle\Entity\StockItem
     */
    protected function getStockItem()
    {
        return $this->stockItem;
    }

    /**
     * @return string
     */
    protected function getCalculatedIcmsCst()
    {
        if ($this->getStockItem()->getZedProduct()->isSt()) {
            return self::CST_ST;
        }

        return self::CST;
    }
}
