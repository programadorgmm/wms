<?php

namespace Natue\Bundle\ShippingBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class OrderIncrementIdLookup
{
    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "IncrementId should not be blank"
     * )
     */
    protected $incrementId;

    /**
     * @return string
     */
    public function getIncrementId()
    {
        return $this->incrementId;
    }

    /**
     * @param string $incrementId
     */
    public function setIncrementId($incrementId)
    {
        $this->incrementId = $incrementId;
    }
}
