<?php

namespace Natue\Bundle\ZedBundle\Entity\Contracts;

interface HasMultiplierQuantity
{
    /**
     * @return int
     */
    public function getMultiplier();
}