<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class PurchaseOrderItem
{
    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "Sku should not be blank"
     * )
     */
    protected $zedProduct;

    /**
     * @var integer
     *
     * @Assert\NotBlank(
     *      message    = "Cost should not be blank"
     * )
     * @Assert\Range(
     *      min        = 0,
     *      minMessage = "Cost should be 0 at least"
     * )
     */
    protected $cost;

    /**
     * @var integer
     *
     * @Assert\NotBlank(
     *      message    = "Quantity of items should not be blank"
     * )
     * @Assert\Range(
     *      min        = 1,
     *      minMessage = "Quantity of items should be 1 at least"
     * )
     */
    protected $quantity;

    /**
     * @var float
     */
    protected $icms_st;

    /**
     * @var integer
     */
    protected $icms;

    /**
     * @var integer
     */
    protected $invoice_cost;

    /**
     * @return string
     */
    public function getZedProduct()
    {
        return $this->zedProduct;
    }

    /**
     * @param string $zedProduct
     *
     * @return void
     */
    public function setZedProduct($zedProduct)
    {
        $this->zedProduct = $zedProduct;
    }

    /**
     * @return integer
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param integer $cost
     *
     * @return void
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param integer $quantity
     *
     * @return void
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return float
     */
    public function getIcmsSt()
    {
        return $this->icms_st;
    }

    /**
     * @param float $icms_st
     */
    public function setIcmsSt($icms_st)
    {
        $this->icms_st = $icms_st;
    }

    /**
     * @return int
     */
    public function getIcms()
    {
        return $this->icms;
    }

    /**
     * @param int $icms
     */
    public function setIcms($icms)
    {
        $this->icms = $icms;
    }

    /**
     * @return int
     */
    public function getInvoiceCost()
    {
        return $this->invoice_cost;
    }

    /**
     * @param int $invoice_cost
     */
    public function setInvoiceCost($invoice_cost)
    {
        $this->invoice_cost = $invoice_cost;
    }

}
