<?php

namespace Natue\Bundle\ShippingBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ProductBarcode
{
    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "Barcode should not be blank"
     * )
     */
    protected $code;
    protected $pickingObservation;

    /**
     * @return string
     */
    public function getPickingObservation()
    {
        return $this->pickingObservation;
    }

    /**
     * @param string $code
     */
    public function setPickingObservation($code)
    {
        $this->pickingObservation = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
}
