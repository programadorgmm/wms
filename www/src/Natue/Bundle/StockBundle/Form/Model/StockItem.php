<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * StockItem form model
 */
class StockItem
{
    /**
     * @var \DateTime
     *
     * @Assert\NotBlank(
     *      message    = "Expiration date should not be blank"
     * )
     * @Assert\Date(
     *      message    = "Expiration date should be a valid date"
     * )
     */
    protected $dateExpiration;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "Barcode should not be blank"
     * )
     */
    protected $barcode;

    /**
     * @param string $barcode
     *
     * @return StockItem
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * @param \DateTime $dateExpiration
     *
     * @return StockItem
     */
    public function setDateExpiration($dateExpiration)
    {
        $this->dateExpiration = $dateExpiration;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateExpiration()
    {
        return $this->dateExpiration;
    }
}
