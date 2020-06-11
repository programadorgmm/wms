<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class PurchaseOrderItemCostAverageUpdate
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
}
