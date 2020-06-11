<?php

namespace Natue\Bundle\DashboardBundle\Service\Concerns;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Natue\Bundle\DashboardBundle\Service\DashboardHandler;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\StockBundle\Repository\StockItemRepository;

class LostedOrdersConcern
{
    /**
     * @var array
     */
    protected $dashboardData;

    /**
     * @var StockItemRepository
     */
    protected $stockItem;

    const IN_PROGRESS_STATUSES = [
        EnumStockItemStatusType::STATUS_ASSIGNED,
        EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
        EnumStockItemStatusType::STATUS_PICKED,
        EnumStockItemStatusType::STATUS_READY_FOR_SHIPPING,
    ];

    /**
     * @param Registry $doctrine
     * @param array $dashboardData
     */
    public function __construct(Registry $doctrine, array $dashboardData)
    {
        $this->dashboardData = $dashboardData;
        $this->stockItem = $doctrine->getRepository('NatueStockBundle:StockItem');
    }

    /**
     * @return array
     */
    public function getDashboardData()
    {
        $this->initialize();
        $this->updateValueInData();

        return $this->dashboardData;
    }

    private function initialize()
    {
        $this->dashboardData['losted_orders'] = [];
    }

    private function getLostedOrdersOnStatuses(array $statuses, $numOldDays)
    {
        return $this->stockItem
            ->getLostedOrdersOnStatuses($statuses, $numOldDays);
    }

    private function updateValueInData()
    {
        $this->dashboardData['losted_orders'] = array_map(function (array $order) {
            $order['diffDays'] = (new \DateTime($order['lostedAt']))->diff(new \DateTime())->days;

            return $order;
        }, $this->getLostedOrdersOnStatuses(
            self::IN_PROGRESS_STATUSES,
            DashboardHandler::NUM_OLD_DAYS
        ));
    }
}