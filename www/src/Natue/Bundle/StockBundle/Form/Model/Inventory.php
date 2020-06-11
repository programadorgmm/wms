<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Inventory
{
    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "Stock Position should not be blank"
     * )
     *
     */
    protected $stockPositionName;

    /**
     * @return string
     */
    public function getStockPositionName()
    {
        return $this->stockPositionName;
    }

    /**
     * @param $stockPositionName
     *
     * @return Inventory
     */
    public function setStockPositionName($stockPositionName)
    {
        $this->stockPositionName = $stockPositionName;

        return $this;
    }
}
