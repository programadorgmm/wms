<?php
namespace Natue\Bundle\DashboardBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Natue\Bundle\DashboardBundle\Service\Concerns\LogisticsProvidersConcern;
use Natue\Bundle\DashboardBundle\Service\Concerns\TotalPickingListByDayConcern;
use Natue\Bundle\DashboardBundle\Service\Concerns\StockItemConcern;
use Natue\Bundle\DashboardBundle\Service\Concerns\LostedOrdersConcern;

/**
 * Class DashboardHandler
 * @package Natue\Bundle\DashboardBundle\Service
 */
class DashboardHandler
{
    const NUM_OLD_DAYS = 5;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var array
     */
    protected $dashboardData = [];

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
    * @return array
    */
    public function getDashboardList()
    {
        $this->populateLogisticProviderList();
        $this->populateStockItemOnConcernStatus();
        $this->populateTotalsPanel();
        $this->populateLostedOrders();

        return $this->dashboardData;
    }

    /**
     * @return void
     */
    private function populateLogisticProviderList()
    {
        $concern = new LogisticsProvidersConcern(
            $this->doctrine,
            $this->dashboardData
        );

        $this->dashboardData = $concern->getDashboardData();
    }

    /**
    * @return void
    */
    private function populateStockItemOnConcernStatus()
    {
        $concern = new StockItemConcern(
            $this->doctrine,
            $this->dashboardData
        );

        $this->dashboardData = $concern->getDashboardData();
    }

    /**
    * @return void
    */
    private function populateTotalsPanel()
    {
        $concern = new TotalPickingListByDayConcern(
            $this->doctrine,
            $this->dashboardData
        );

        $this->dashboardData = $concern->getDashboardData();
    }

    /**
    * @return void
    */
    private function populateLostedOrders()
    {
        $concern = new LostedOrdersConcern(
            $this->doctrine,
            $this->dashboardData
        );

        $this->dashboardData = $concern->getDashboardData();
    }
}
