<?php

namespace Natue\Bundle\InvoiceBundle\Taxman;

use Natue\Bundle\InvoiceBundle\Taxman\Contracts\InvoiceInterface;

/**
 * Class Invoice
 * @package Natue\Bundle\InvoiceBundle\Taxman
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class Invoice implements InvoiceInterface
{
    /**
     * @var array
     */
    protected $invoice = [
        'identification' => [],
        'recipient'      => [],
        'products'       => [],
        'total'          => [],
    ];

    /**
     * @param array $identification
     * @return $this
     */
    public function withIdentification(array $identification)
    {
        $this->invoice['identification'] = array_replace([
            'invoice_code'     => null,
            'invoice_series'   => null,
            'invoice_number'   => null,
            'payment_type'     => null,
            'destination_type' => null,
            'creation_date'    => null,
        ], $identification);

        return $this;
    }

    /**
     * @param array $recipient
     * @return $this
     */
    public function withRecipient(array $recipient)
    {
        $this->invoice['recipient'] = array_replace_recursive([
            'document'           => null,
            'document_type'      => null,
            'state_registration' => null,
            'name'               => null,
            'address'            => [
                'zip_code'   => null,
                'street'     => null,
                'number'     => null,
                'complement' => null,
                'district'   => null,
                'city_code'  => null,
                'city_name'  => null,
                'state'      => null,
            ],
        ], $recipient);

        return $this;
    }

    /**
     * @param array $product
     * @return $this
     */
    public function withProduct(array $product)
    {
        $this->invoice['products'][] = array_replace_recursive([
            'sku'                    => null,
            'bar_code'               => null,
            'description'            => null,
            'ncm_code'               => null,
            'cest_code'              => null,
            'tipi_code'              => null,
            'cfop_code'              => null,
            'comercial_measure_unit' => null,
            'quantity'               => null,
            'unit_price'             => null,
            'appears_total'          => null,
            'tribute_bar_code'       => null,
            'tribute_measure_unit'   => null,
            'tribute_quantity'       => null,
            'freight_price'          => null,
            'insurance_price'        => null,
            'discount_price'         => null,
            'other_price'            => null,
            'taxes'                  => [
                'icms'            => [
                    'origin'                  => null,
                    'cst'                     => null,
                    'modality'                => null,
                    'aliquot'                 => null,
                    'base_value'              => null,
                    'value'                   => null,
                    'aliquot_for_donation'    => null,
                    'aliquot_for_destination' => null,
                ],
                'pis'             => [
                    'cst'        => null,
                    'aliquot'    => null,
                    'base_value' => null,
                    'value'      => null,
                ],
                'cofins'          => [
                    'cst'        => null,
                    'aliquot'    => null,
                    'base_value' => null,
                    'value'      => null,
                ],
                'estimated_taxes' => null,
            ],
        ], $product);

        return $this;
    }

    /**
     * @param array $products
     * @return $this
     */
    public function withProducts(array $products)
    {
        foreach ($products as $product) {
            $this->withProduct($product);
        }

        return $this;
    }

    /**
     * @param array $totals
     * @return $this
     */
    public function withTotals(array $totals)
    {
        $this->invoice['total'] = array_replace_recursive([
            'icms_total' => [
                'calc_base'          => null,
                'total'              => null,
                'unencumbered_total' => null,
                'st_calc_base'       => null,
                'st_total'           => null,
                'product_total'      => null,
                'shipment_total'     => null,
                'insurance_total'    => null,
                'discount_total'     => null,
                'II_total'           => null,
                'IPI_total'          => null,
                'pis'                => null,
                'cofins'             => null,
                'other_values'       => null,
                'nfe_total'          => null,
                'estimated_taxes'    => null,
            ],
        ], $totals);

        return $this;
    }

    /**
     * @param array $additionalInfo
     * @return $this
     */
    public function withAdditionalInfo(array $additionalInfo)
    {
        $this->invoice['additional_info'] = array_replace([
            'message'       => null,
            'is_appendable' => false,
        ], $additionalInfo);

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->invoice;
    }
}
