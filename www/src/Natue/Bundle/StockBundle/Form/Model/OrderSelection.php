<?php

namespace Natue\Bundle\StockBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class OrderSelection
{
    /**
     * @var string
     */
    private $incrementId;

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
