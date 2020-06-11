<?php

namespace Natue\Bundle\InvoiceBundle\Taxes\Calculators;

use Natue\Bundle\InvoiceBundle\Taxes\Contracts\CalculatorInterface;
use Natue\Bundle\InvoiceBundle\Taxes\Entities\AbstractTax;
use Natue\Bundle\InvoiceBundle\Taxes\Exceptions\PisCofinsNotFoundException;
use Natue\Bundle\StockBundle\Entity\StockItem;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;

/**
 * Class AbstractPisCofinsCalculator
 * @package Natue\Bundle\InvoiceBundle\Taxes\Calculators
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
abstract class AbstractPisCofinsCalculator implements CalculatorInterface
{
    const DEFAULT_TYPES_AND_ALIQUOTS = [
        'default'       => [
            'cst'     => '01',
            'aliquot' => 0.00,
        ],
        'monofasico'    => [
            'cst'     => '04',
            'aliquot' => 0.00,
        ],
        'aliquota zero' => [
            'cst'     => '06',
            'aliquot' => 0.00,
        ],
        'suspensão'     => [
            'cst'     => '09',
            'aliquot' => 0.00,
        ],
        'books'         => [
            'cst'     => '08',
            'aliquot' => 0.00,
        ],
    ];
    
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
     * @param int                                        $quantity
     */
    public function __construct(StockItem $stockItem, $quantity)
    {
        $this->stockItem = $stockItem;
        $this->quantity = $quantity;
    }

    /**
     * @return \Natue\Bundle\InvoiceBundle\Taxes\Entities\Pis
     */
    abstract protected function getTaxEntity();

    /**
     * @return array
     */
    abstract protected function getTypesAndAliquots();

    /**
     * @return mixed
     */
    public function getCalculatedTax()
    {
        $cstAndAliquot = $this->getCstAndAliquot(
            $this->stockItem->getZedProduct()
        );

        $cost = $this->stockItem
            ->getPurchaseOrderItem()
            ->getInvoiceCost();

        $tax = $this->getTaxEntity();
        $tax->setCst($cstAndAliquot['cst']);
        $tax->setAliquot($cstAndAliquot['aliquot']);
        $tax->setBaseValue($this->quantity * $cost);
        $tax->setValue($this->calculateValue($tax));

        return $tax;
    }

    /**
     * @param \Natue\Bundle\ZedBundle\Entity\ZedProduct $product
     * @return array
     * @throes \Natue\Bundle\InvoiceBundle\Taxes\Exceptions\PisCofinsNotFoundException
     */
    protected function getCstAndAliquot(ZedProduct $product)
    {
        $typesAndAliquots = $this->getTypesAndAliquots();

        if ($product->isBook()) {
            return $typesAndAliquots['books'];
        }

        $pisCofinsAttribute = strtolower($product->getPisCofins());

        if (empty($pisCofinsAttribute) || '-' === $pisCofinsAttribute) {
            return $typesAndAliquots['default'];
        }

        if (! array_key_exists($pisCofinsAttribute, $typesAndAliquots)) {
            throw new PisCofinsNotFoundException;
        }

        return $typesAndAliquots[$pisCofinsAttribute];
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxes\Entities\AbstractTax $pis
     * @return int
     */
    protected function calculateValue(AbstractTax $pis)
    {
        return (int) ($pis->getBaseValue() * ($pis->getAliquot() / 100));
    }
}
