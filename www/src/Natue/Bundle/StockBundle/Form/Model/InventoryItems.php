<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class InventoryItems
{
    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "Inventory item barcode should not be blank"
     * )
     */
    protected $barcode;

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
    protected $quantity;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "Position should not be blank"
     * )
     */

    protected $position;

    /**
     * @return string
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * @param string $barcode
     *
     * @return $this
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     *
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }
}
