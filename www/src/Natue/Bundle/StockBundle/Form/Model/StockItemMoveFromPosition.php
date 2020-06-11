<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StockItemMoveFromPosition
 */
class StockItemMoveFromPosition
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
     * @var integer
     *
     * @Assert\NotBlank(
     *      message = "The name of the stock position 'to' should not be blank"
     * )
     */
    protected $newStockPositionId;

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
}
