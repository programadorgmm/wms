<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StockItemMove
 */
class StockItemMove
{
    /**
     * @var integer
     *
     * @Assert\NotBlank(
     *      message = "The name of the stock position 'from' should not be blank"
     * )
     */
    protected $oldStockPositionId;

    /**
     * @var bool
     */
    protected $oldStockPositionCheck;

    /**
     * @var bool
     */
    protected $new_stock_positionCheck;

    /**
     * @var bool
     */
    protected $quantityCheck;

    /**
     * @var integer
     *
     * @Assert\NotBlank(
     *      message = "The name of the stock position 'to' should not be blank"
     * )
     */
    protected $newStockPositionId;

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
     * @var string
     *
     * @Assert\NotBlank(
     *      message = "Barcode should not be blank"
     * )
     */
    protected $barcode;

    /**
     * @return integer
     */
    public function getOldStockPositionId()
    {
        return $this->oldStockPositionId;
    }

    /**
     * @param integer $oldStockPositionId
     *
     * @return void
     */
    public function setOldStockPositionId($oldStockPositionId)
    {
        $this->oldStockPositionId = $oldStockPositionId;
    }
    
    /**
     * @return boolean
     */
    public function getOldStockPositionCheck()
    {
        return $this->oldStockPositionCheck;
    }

    /**
     * @param boolean $oldStockPositionCheck
     */
    public function setOldStockPositionCheck($oldStockPositionCheck)
    {
        $this->oldStockPositionCheck = $oldStockPositionCheck;
    }

    /**
     * @return boolean
     */
    public function getNewStockPositionCheck()
    {
        return $this->newStockPositionCheck;
    }

    /**
     * @param boolean $newStockPositionCheck
     */
    public function setNewStockPositionCheck($newStockPositionCheck)
    {
        $this->newStockPositionCheck = $newStockPositionCheck;
    }

    /**
     * @return boolean
     */
    public function getQuantityCheck()
    {
        return $this->quantityCheck;
    }
     /**
     * @param boolean $quantityCheck
     */
    public function setQuantityCheck($quantityCheck)
    {
        $this->quantityCheck = $quantityCheck;
    }

    /**
     * @return integer
     */
    public function getNewStockPositionId()
    {
        return $this->newStockPositionId;
    }

    /**
     * @param integer $newStockPositionId
     *
     * @return void
     */
    public function setNewStockPositionId($newStockPositionId)
    {
        $this->newStockPositionId = $newStockPositionId;
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
     * @return string
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * @param string $barcode
     *
     * @return void
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;
    }
}
