<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZedOrderItemsReadyForShipping
 *
 * @ORM\Table(name="zed_order_items_ready_for_shipping")
 *
 * @ORM\Entity(readOnly=true)
 */
class ZedOrderItemReadyForShipping
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    private $zedOrder;

    public function getZedOrder()
    {
        return $this->zedOrder;
    }

    /**
     * Set zedOrder
     *
     * @param integer $zedOrder
     * @return ZedOrderItemReadyForShipping
     */
    public function setZedOrder($zedOrder)
    {
        $this->zedOrder = $zedOrder;

        return $this;
    }
}
