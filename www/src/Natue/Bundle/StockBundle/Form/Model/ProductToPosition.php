<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

use Natue\Bundle\StockBundle\Entity\StockPosition;

class ProductToPosition
{
    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message = "StockItemBarcode should not be blank"
     * )
     */
    private $stockItemBarcode;

    /**
     * @var StockPosition
     *
     * @Assert\NotBlank(
     *      message = "Position should not be blank"
     * )
     */
    private $position;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message = "Transition should not be blank"
     * )
     * @Assert\Choice(
     *      choices = {"ready", "damaged", "expired"},
     *      message = "Choose a valid transition."
     * )
     */
    private $transition;

    /**
     * @return StockPosition
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param StockPosition $position
     */
    public function setPosition(StockPosition $position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getTransition()
    {
        return $this->transition;
    }

    /**
     * @param string $transition
     */
    public function setTransition($transition)
    {
        $this->transition = $transition;
    }

    /**
     * @return string
     */
    public function getStockItemBarcode()
    {
        return $this->stockItemBarcode;
    }

    /**
     * @param string $stockItemBarcode
     */
    public function setStockItemBarcode($stockItemBarcode)
    {
        $this->stockItemBarcode = $stockItemBarcode;
    }
}
