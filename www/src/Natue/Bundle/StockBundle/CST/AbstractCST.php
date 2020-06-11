<?php

namespace Natue\Bundle\StockBundle\CST;

/**
 * Class AbstractCST
 * @package Natue\Bundle\StockBundle\CST
 */
abstract class AbstractCST
{
    /**
     * @var integer
     */
    private $value;

    /**
     * @var string
     */
    private $name;

    /**
     * AbstractCST constructor.
     *
     * @param int $value
     * @param string $name
     */
    public function __construct($value, $name)
    {
        $this->value = $value;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('CST %s - %s', $this->getValue(), $this->getName());
    }
}