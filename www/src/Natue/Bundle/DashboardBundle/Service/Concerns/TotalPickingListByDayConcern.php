<?php

namespace Natue\Bundle\DashboardBundle\Service\Concerns;

use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Class TotalPickingListByDayConcern
 * @package Natue\Bundle\DashboardBundle\Service\Concerns
 */
class TotalPickingListByDayConcern
{
    /**
    * @var Natue\Bundle\ZedBundle\Entity\ZedOrder
    */
    protected $zedOrder;

    /**
    * @var array
    */
    protected $dashboardData;

    /**
    * @param Registry $doctrine
    * @param Array    $dashboardData
    */
    public function __construct(Registry $doctrine, Array $dashboardData)
    {
        $this->dashboardData = $dashboardData;
        $this->zedOrder      = $doctrine->getRepository('NatueZedBundle:ZedOrder');
    }

    /**
    * @return array
    */
    public function getDashboardData()
    {
        $this->initialize();

        return $this->dashboardData;
    }

    /**
    * @return array
    */
    private function initialize()
    {
        $zedOrder = $this->zedOrder->getPickingListTodayByLogisticProvider();

        foreach ($zedOrder as $order) {
            $this->setValueInData($order);
        }

        return $this->dashboardData;
    }

    /**
    * @return void
    */
    private function setValueInData($order)
    {
        list($total, $nameInternal) = [
            $order['total'], $order['nameInternal']
        ];

        $this->dashboardData['providers'][strtolower($nameInternal)]['today'] = $total;
        $this->dashboardData['total']['today']                               += $total;
    }
}
