<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

use Natue\Bundle\CoreBundle\Validator\Constraints as NatueAssert;

class PurchaseOrderItemDistribution
{
    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "Barcode should not be blank"
     * )
     */
    protected $barcode;

    /**
     * @var \DateTime
     *
     * @Assert\NotBlank(
     *      message = "Expiration data should not be blank"
     * )
     * @NatueAssert\ContainsNotExpired(
     *      message = "DateExpiration should not be past due."
     * )
     */
    private $dateExpiration;

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
     *      message = "Position should not be blank"
     * )
     */
    protected $position;

    /**
     * @param string $barcode
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;
    }

    /**
     * @return string
     */
    public function getBarcode()
    {
        return $this->barcode;
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
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param \DateTime $dateExpiration
     */
    public function setDateExpiration($dateExpiration)
    {
        $this->dateExpiration = $dateExpiration;
    }

    /**
     * @return \DateTime
     */
    public function getDateExpiration()
    {
        return $this->dateExpiration;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }
}
