<?php

namespace Natue\Bundle\ShippingBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ExplicitPickingOrders
{

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "OrdersList should not be blank"
     * )
     */
    protected $ordersList;

    /**
     * @return string
     */
    public function getOrdersList()
    {
        return $this->ordersList;
    }

    /**
     * @param string $ordersList
     */
    public function setOrdersList($ordersList)
    {
        $this->ordersList = $ordersList;
    }
}
