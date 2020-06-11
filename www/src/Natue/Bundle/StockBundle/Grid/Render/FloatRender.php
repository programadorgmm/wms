<?php

namespace Natue\Bundle\StockBundle\Grid\Render;

use PedroTeixeira\Bundle\GridBundle\Grid\Render\RenderAbstract;

class FloatRender extends RenderAbstract
{
    /**
     * @return string
     */
    public function render()
    {
        return $this->getValue() / 100;
    }
}