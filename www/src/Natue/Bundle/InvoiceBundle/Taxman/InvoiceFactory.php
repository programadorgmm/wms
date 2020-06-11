<?php

namespace Natue\Bundle\InvoiceBundle\Taxman;

use Doctrine\Common\Collections\Collection;
use Natue\Bundle\InvoiceBundle\Entity;
use Natue\Bundle\InvoiceBundle\Exceptions\IcmsStAbsentException;
use Natue\Bundle\InvoiceBundle\Taxes\TaxesFactory;
use Natue\Bundle\InvoiceBundle\Util\Contracts\Filter;
use Natue\Bundle\StockBundle\Entity\StockItem;

/**
 * Class InvoiceFactory
 * @package Natue\Bundle\InvoiceBundle\Taxman
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class InvoiceFactory
{
    const CFOP = 5927;
    const ST_TOTAL = 0;
    const II_TOTAL = 0;
    const IPI_TOTAL = 0;
    const OTHER_PRICE = 0;
    const ST_CALC_BASE = 0;
    const OTHER_VALUES = 0;
    const PAYMENT_TYPE = 2;
    const APPEARS_TOTAL = 1;
    const FREIGHT_PRICE = 0;
    const DISCOUNT_PRICE = 0;
    const DISCOUNT_TOTAL = 0;
    const SHIPMENT_TOTAL = 0;
    const INSURANCE_PRICE = 0;
    const MEASURE_UNIT = 'UN';
    const INSURANCE_TOTAL = 0;
    const DESTINATION_TYPE = 1;
    const UNENCUMBERED_TOTAL = 0;
    const APPENDABLE_MESSAGE = false;
    const DEFAULT_ESTIMATED_TOTAL_TAX_RATE = 31.45;
    const BASE_MESSAGE = 'NF EMITIDA DE ACORDO COM O ARTIGO 2 DO DECRETO 61720/15 CONFORME ARTIGO 125 DO RICMS/SP';
    const PRODUCT_MESSAGE = 'ITEM %s: vICMS %.2f';
    const RECIPIENT = [
        'document_type'      => 'CNPJ',
        'document'           => '17018091000195',
        'state_registration' => '145721250112',
        'name'               => 'NATUE COMERCIO E IMPORTACAO DE COSMETICOS E PRODUTOS ALIMENT',
        'address'            => [
            'zip_code'   => '05307000',
            'street'     => 'R MAJOR PALADINO',
            'number'     => '128',
            'complement' => 'Galpão 14',
            'district'   => 'Vila Ribeiro de Barros',
            'city_code'  => '3550308',
            'city_name'  => 'São Paulo',
            'state'      => 'SP',
        ],
    ];

    /**
     * @var \Natue\Bundle\InvoiceBundle\Taxes\TaxesFactory
     */
    protected $taxesFactory;

    /**
     * @var \Natue\Bundle\InvoiceBundle\Util\Contracts\Filter
     */
    protected $stringFilter;

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxes\TaxesFactory    $taxesFactory
     * @param \Natue\Bundle\InvoiceBundle\Util\Contracts\Filter $stringFilter
     */
    public function __construct(TaxesFactory $taxesFactory, Filter $stringFilter)
    {
        $this->taxesFactory = $taxesFactory;
        $this->stringFilter = $stringFilter;
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Entity\Invoice $invoiceEntity
     * @return \Natue\Bundle\InvoiceBundle\Taxman\Invoice
     */
    public function create(Entity\Invoice $invoiceEntity)
    {
        $identification        = $this->buildIdentification($invoiceEntity);
        $itemsGroupedBySku     = $this->groupStockItemsBySku($invoiceEntity->getItems());
        $products              = $this->buildProducts($itemsGroupedBySku);
        $totals                = $this->buildTotals($products);
        $additionalInfoMessage = $this->buildAdditionalInfoMessage($itemsGroupedBySku);

        $invoice = (new Invoice())
            ->withIdentification($identification)
            ->withRecipient(self::RECIPIENT)
            ->withProducts($products)
            ->withTotals($totals);

        if (!$additionalInfoMessage) {
            return $invoice;
        }

        return $invoice->withAdditionalInfo([
            'message'       => $additionalInfoMessage,
            'is_appendable' => false,
        ]);
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Entity\Invoice $invoiceEntity
     * @return array
     */
    protected function buildIdentification(Entity\Invoice $invoiceEntity)
    {
        return [
            'invoice_code'     => $invoiceEntity->getId(),
            'invoice_series'   => $invoiceEntity->getInvoiceNumber()->getSeries(),
            'invoice_number'   => $invoiceEntity->getInvoiceNumber()->getNumber(),
            'payment_type'     => self::PAYMENT_TYPE,
            'destination_type' => self::DESTINATION_TYPE,
            'creation_date'    => date('c'),
        ];
    }

    /**
     * @param array $itemsGroupedBySku
     *  $itemsGroupedBySku['item'] Natue\Bundle\StockBundle\Entity\StockItem
     *  $itemsGroupedBySku['quantity'] int
     * @return array[]
     */
    protected function buildProducts(array $itemsGroupedBySku)
    {
        $products = [];

        foreach ($itemsGroupedBySku as $order => $groupedItem) {
            $products[$order] = $this->buildProduct(
                $groupedItem['item'],
                $groupedItem['quantity']
            );
        }

        return $products;
    }

    /**
     * @param \Natue\Bundle\StockBundle\Entity\StockItem $stockItem
     * @param int                                        $quantity
     * @return array
     */
    protected function buildProduct(StockItem $stockItem, $quantity)
    {
        $purchaseOrderItem = $stockItem->getPurchaseOrderItem();

        $barcode     = str_pad(filter_var($stockItem->getBarcode(), FILTER_SANITIZE_NUMBER_INT), 12, '0', STR_PAD_LEFT);
        $ncm         = str_pad($stockItem->getZedProduct()->getNcm(), 8, '0', STR_PAD_LEFT);
        $description = $this->stringFilter->filter($stockItem->getZedProduct()->getName());
        $unitPrice   = $purchaseOrderItem->getInvoiceCost();
        if (!$unitPrice) {
            $unitPrice = $purchaseOrderItem->getCost();
        }
        $unitPrice   = $this->formatCurrency($unitPrice);

        $taxes           = $this->taxesFactory->create($stockItem, $quantity);
        $icms            = $taxes->getIcms();
        $icmsBaseValue   = $this->formatCurrency($icms->getBaseValue());
        $icmsValue       = $this->formatCurrency($icms->getValue());
        $pis             = $taxes->getPis();
        $pisBaseValue    = $this->formatCurrency($pis->getBaseValue());
        $pisValue        = $this->formatCurrency($pis->getValue());
        $cofins          = $taxes->getCofins();
        $cofinsBaseValue = $this->formatCurrency($cofins->getBaseValue());
        $cofinsValue     = $this->formatCurrency($cofins->getValue());
        $estimatedTaxes  = $this->calculateProductEstimatedTaxes($stockItem, $quantity);

        return [
            'sku'                    => $stockItem->getZedProduct()->getSku(),
            'bar_code'               => $barcode,
            'description'            => $description,
            'ncm_code'               => $ncm,
            'cest_code'              => $stockItem->getZedProduct()->getCest(),
            'tipi_code'              => null,
            'cfop_code'              => self::CFOP,
            'comercial_measure_unit' => self::MEASURE_UNIT,
            'quantity'               => $quantity,
            'unit_price'             => $unitPrice,
            'appears_total'          => self::APPEARS_TOTAL,
            'tribute_bar_code'       => $barcode,
            'tribute_measure_unit'   => self::MEASURE_UNIT,
            'tribute_quantity'       => $quantity,
            'freight_price'          => self::FREIGHT_PRICE,
            'insurance_price'        => self::INSURANCE_PRICE,
            'discount_price'         => self::DISCOUNT_PRICE,
            'other_price'            => self::OTHER_PRICE,
            'taxes'                  => [
                'icms'            => [
                    'origin'                  => $icms->getOrigin(),
                    'cst'                     => $icms->getCst(),
                    'modality'                => $icms->getModality(),
                    'aliquot'                 => $icms->getAliquot(),
                    'base_value'              => $icmsBaseValue,
                    'value'                   => $icmsValue,
                    'aliquot_for_donation'    => $icms->getAliquotForDonation(),
                    'aliquot_for_destination' => $icms->getAliquotForDestination(),
                ],
                'pis'             => [
                    'cst'        => $pis->getCst(),
                    'aliquot'    => $pis->getAliquot(),
                    'base_value' => $pisBaseValue,
                    'value'      => $pisValue,
                ],
                'cofins'          => [
                    'cst'        => $cofins->getCst(),
                    'aliquot'    => $cofins->getAliquot(),
                    'base_value' => $cofinsBaseValue,
                    'value'      => $cofinsValue,
                ],
                'estimated_taxes' => $estimatedTaxes,
            ],
        ];
    }

    /**
     * @param array $products
     * @return array
     */
    protected function buildTotals(array $products)
    {
        $totals = [
            'pis'             => 0,
            'cost'            => 0,
            'cofins'          => 0,
            'icms_value'      => 0,
            'icms_base_value' => 0,
            'estimated_taxes' => 0,
        ];

        $totals = array_reduce($products, function ($totals, array $product) {
            $totals['cost'] += $product['quantity'] * $product['unit_price'];
            $totals['pis'] += $product['taxes']['pis']['value'];
            $totals['cofins'] += $product['taxes']['cofins']['value'];
            $totals['icms_value'] += $product['taxes']['icms']['value'];
            $totals['estimated_taxes'] += $product['taxes']['estimated_taxes'];
            $totals['icms_base_value'] += $product['taxes']['icms']['base_value'];

            return $totals;
        }, $totals);

        return [
            'icms_total' => [
                'calc_base'          => $totals['icms_base_value'],
                'total'              => $totals['icms_value'],
                'unencumbered_total' => self::UNENCUMBERED_TOTAL,
                'st_calc_base'       => self::ST_CALC_BASE,
                'st_total'           => self::ST_TOTAL,
                'product_total'      => $totals['cost'],
                'shipment_total'     => self::SHIPMENT_TOTAL,
                'insurance_total'    => self::INSURANCE_TOTAL,
                'discount_total'     => self::DISCOUNT_TOTAL,
                'II_total'           => self::II_TOTAL,
                'IPI_total'          => self::IPI_TOTAL,
                'pis'                => $totals['pis'],
                'cofins'             => $totals['cofins'],
                'other_values'       => self::OTHER_VALUES,
                'nfe_total'          => $totals['cost'],
                'estimated_taxes'    => $totals['estimated_taxes'],
            ],
        ];
    }

    /**
     * @param array $itemsGroupedBySku
     *  $itemsGroupedBySku[0]['item'] Natue\Bundle\StockBundle\Entity\StockItem
     *  $itemsGroupedBySku[0]['quantity'] int
     * @return array
     */
    protected function buildAdditionalInfoMessage(array $itemsGroupedBySku)
    {
        return self::BASE_MESSAGE;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection|\Natue\Bundle\StockBundle\Entity\StockItem[] $stockItems
     * @return array
     */
    protected function groupStockItemsBySku(Collection $stockItems)
    {
        return array_values(
            array_reduce($stockItems->toArray(), function (array $grouping, StockItem $stockItem) {
                $sku = $stockItem->getZedProduct()->getSku();

                if (array_key_exists($sku, $grouping)) {
                    $grouping[$sku]['quantity']++;

                    return $grouping;
                }

                $grouping[$sku] = [
                    'quantity' => 1,
                    'item'     => $stockItem,
                ];

                return $grouping;
            }, [])
        );
    }

    /**
     * @param \Natue\Bundle\StockBundle\Entity\StockItem $stockItem
     * @param int                                        $quantity
     * @return float
     */
    protected function calculateProductEstimatedTaxes(StockItem $stockItem, $quantity)
    {
        $baseValue = $stockItem->getPurchaseOrderItem()->getCost();
        $taxRate   = $stockItem->getZedProduct()->getEstimatedTotalTaxesAliquot()
            ?: self::DEFAULT_ESTIMATED_TOTAL_TAX_RATE;

        return $this->formatCurrency($quantity * ($baseValue * $taxRate / 100));
    }

    /**
     * @param int $cents
     * @return float
     */
    protected function formatCurrency($cents)
    {
        return (float) sprintf('%2.f', $cents / 100);
    }
}
