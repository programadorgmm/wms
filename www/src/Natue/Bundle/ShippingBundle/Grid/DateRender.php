<?php

namespace Natue\Bundle\ShippingBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\Render\DateTime;

/**
 * Class DateRender
 * @package Natue\Bundle\StockBundle\Grid\Render
 *
 * @method \DateTime getValue
 */
class DateRender extends DateTime
{
    /**
     * @return string
     */
    public function render()
    {
        $this->getValue()->setTimezone(new \DateTimeZone('America/Sao_Paulo'));

        return parent::render();
    }
}