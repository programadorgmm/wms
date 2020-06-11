<?php

namespace Natue\Bundle\DashboardBundle\Service\Concerns;

use Natue\Bundle\ShippingBundle\Entity\ShippingLogisticsProvider;
use Natue\Bundle\ZedBundle\Entity\ZedOrderItem;
use Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * Class LogisticsProvidersConcern
 * @package Natue\Bundle\DashboardBundle\Service\Concerns
 */
class LogisticsProvidersConcern
{
    /**
     * @var use Natue\Bundle\ZedBundle\Entity\ZedOrderItem
     */
    protected $zedOrderItem;

    /**
     * @var array
     */
    protected $dashboardData;

    /**
     * @var ShippingLogisticsProviderRepository
     */
    protected $shippingLogisticsProvider;

    /**
     * @param Registry $doctrine
     * @param array $dashboardData
     */
    public function __construct(Registry $doctrine, Array $dashboardData)
    {
        $this->dashboardData = $dashboardData;
        $this->zedOrderItem = $doctrine->getRepository('NatueZedBundle:ZedOrderItem');
        $this->shippingLogisticsProvider = $doctrine->getRepository('NatueShippingBundle:ShippingLogisticsProvider');
    }

    /**
     * @return array
     */
    public function getDashboardData()
    {
        $this->initialize();
        $this->updateValueInData();
        $this->updateTotalInProgress();

        return $this->dashboardData;
    }

    /**
     * @return void
     */
    private function initialize()
    {
        $providers = $this->logisticsProvider();

        foreach ($providers as $provider) {
            $this->dashboardData['providers'][$provider]['waiting_for_picking'] = 0;
            $this->dashboardData['providers'][$provider]['assigned'] = 0;
            $this->dashboardData['providers'][$provider]['today'] = 0;
            $this->dashboardData['providers'][$provider]['in_process'] = 0;
        }

        $this->dashboardData['total']['waiting_for_picking'] = 0;
        $this->dashboardData['total']['assigned'] = 0;
        $this->dashboardData['total']['today'] = 0;
        $this->dashboardData['total']['in_process'] = 0;
    }

    /**
     * @return void
     */
    private function updateValueInData()
    {
        $totalItem = $this->zedOrderItem->getTotalItemByProviderList();

        foreach ($totalItem as $item) {
            $this->setValueInData($item);
            $this->updateValueInProcess($item);
        }
    }

    /**
     * @return void
     */
    private function updateTotalInProgress()
    {
        $this->dashboardData['total']['in_process'] += (
            $this->dashboardData['total']['assigned'] +
            $this->dashboardData['total']['waiting_for_picking']
        );
    }

    /**
     * @return void
     */
    private function updateValueInProcess($item)
    {
        $provider = strtolower($item['provider']);

        $this->dashboardData['providers'][$provider]['in_process'] = (
            $this->dashboardData['providers'][$provider]['assigned'] +
            $this->dashboardData['providers'][$provider]['waiting_for_picking']
        );
    }

    /**
     * @return void
     */
    private function setValueInData($item)
    {
        list($provider, $status, $total) = [
            strtolower($item['provider']), $item['status'], $item['total'],
        ];

        $this->dashboardData['providers'][$provider][$status] = (int)$total;
        $this->dashboardData['total'][$status] += (int)$total;
    }

    /**
     * @return array
     */
    public function logisticsProvider()
    {
        return
            array_map(function (ShippingLogisticsProvider $provider) {
                return strtolower($provider->getNameInternal());
            }, $this->shippingLogisticsProvider->findByActive(true));
    }
}
