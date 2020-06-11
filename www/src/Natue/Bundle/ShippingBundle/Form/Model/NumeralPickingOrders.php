<?php

namespace Natue\Bundle\ShippingBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

use Natue\Bundle\ShippingBundle\Entity\ShippingLogisticsProvider;

class NumeralPickingOrders
{

    /**
     * @var boolean
     */
    protected $monoSku;

    /**
     * @var integer
     */
    protected $ordersAmount;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *      message    = "Logistics Provider should not be blank"
     * )
     */
    protected $logisticsProvider;

    /**
     * @return bool
     */
    public function getMonoSku()
    {
        return $this->monoSku;
    }

    /**
     * @param bool $monoSku
     */
    public function setMonoSku($monoSku)
    {
        $this->monoSku = $monoSku;
    }

    /**
     * @return ShippingLogisticsProvider
     */
    public function getLogisticsProvider()
    {
        return $this->logisticsProvider;
    }

    /**
     * @param ShippingLogisticsProvider $logisticsProvider
     */
    public function setLogisticsProvider($logisticsProvider)
    {
        $this->logisticsProvider = $logisticsProvider;
    }

    /**
     * @return int
     */
    public function getOrdersAmount()
    {
        return $this->ordersAmount;
    }

    /**
     * @param int $ordersAmount
     */
    public function setOrdersAmount($ordersAmount)
    {
        $this->ordersAmount = $ordersAmount;
    }
}
