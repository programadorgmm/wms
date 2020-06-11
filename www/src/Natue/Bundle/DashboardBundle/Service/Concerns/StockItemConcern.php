<?php

namespace Natue\Bundle\DashboardBundle\Service\Concerns;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Natue\Bundle\DashboardBundle\Service\DashboardHandler;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\StockBundle\Repository\StockItemRepository;
use Natue\Bundle\ZedBundle\Repository\ZedOrderItemRepository as ZedOrderItemRepository;

/**
 * Class StockItemConcern
 * @package Natue\Bundle\DashboardBundle\Service\Concerns
 */
class StockItemConcern
{
    /**
     * @var ZedOrderItemRepository
     */
    protected $zedOrderItem;

    /**
     * @var StockItemRepository
     */
    protected $stockItem;

    /**
     * @var array
     */
    protected $dashboardData;

    const LATE_DAYS = 3;

    /**
     * @var LogisticsProvidersConcern
     */
    protected $logisticsProvidersConcern;

    const CONCERN_STATUS = [
        EnumStockItemStatusType::STATUS_ASSIGNED,
        EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING,
        EnumStockItemStatusType::STATUS_PICKED,
        EnumStockItemStatusType::STATUS_READY_FOR_SHIPPING,
    ];

    const CONCERN_STATUSES_TO_LIST = self::CONCERN_STATUS;

    /**
     * @param Registry $doctrine
     * @param array $dashboardData
     */
    public function __construct(Registry $doctrine, array $dashboardData)
    {
        $this->dashboardData = $dashboardData;
        $this->zedOrderItem = $doctrine->getRepository('NatueZedBundle:ZedOrderItem');
        $this->stockItem = $doctrine->getRepository('NatueStockBundle:StockItem');
        $this->logisticsProvidersConcern = new LogisticsProvidersConcern($doctrine, $dashboardData);
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

    /**
     * @return void
     */
    private function initialize()
    {
        $oldDates = $this->oldDays();
        $providers = $this->logisticsProvidersConcern->logisticsProvider();

        $this->dashboardData['headers'] = $providers;

        $mapProviders = function ($date) use ($providers) {
            return [
                'date'      => $date,
                'formatted' => (new \DateTime($date))->format('d/m/Y'),
                'providers' => array_combine(
                    $providers,
                    array_fill(0, count($providers), 0)
                ),
            ];
        };

        $mapStatuses = function () use ($mapProviders, $oldDates) {
            return ['dates' => array_combine($oldDates, array_map($mapProviders, $oldDates))];
        };

        $this->dashboardData['stockItem'] = [];
        $this->dashboardData['statuses'] = array_combine(
            self::CONCERN_STATUS,
            array_map($mapStatuses, self::CONCERN_STATUS)
        );

        $this->dashboardData['monoSku'] = $mapStatuses();

        $this->dashboardData['totals'] = array_combine(
            self::CONCERN_STATUS,
            array_fill(0, count(self::CONCERN_STATUS), 0)
        );
        $this->dashboardData['totals']['monoSku'] = 0;
        $this->dashboardData['progress'] = [
            'assigned'    => 0,
            'in_progress' => 0,
            'shipped'     => 0,
        ];
    }

    /**
     * @return void
     */
    private function updateValueInData()
    {
        $stockItemsTable = $this->zedOrderItem->getLastOldDatesStockItemsTotals(self::CONCERN_STATUS, DashboardHandler::NUM_OLD_DAYS);
        $stockItems = $this->zedOrderItem->getLastOldDatesStockItems(self::CONCERN_STATUSES_TO_LIST, DashboardHandler::NUM_OLD_DAYS);
        $currentStatusesTable = $this->stockItem->countCurrentItemsOnStatuses(self::CONCERN_STATUS);

        foreach ($stockItemsTable as $row) {
            $this->setValueInData($row);
        }

        $this->setStockItemsInData($stockItems);

        foreach ($currentStatusesTable as $status) {
            if ($status['status'] === EnumStockItemStatusType::STATUS_ASSIGNED) {
                $this->dashboardData['progress']['assigned'] += (int)$status['total'];
                continue;
            }

            if ($status['status'] === EnumStockItemStatusType::STATUS_SOLD) {
                $this->dashboardData['progress']['shipped'] += (int)$status['total'];
                continue;
            }

            $this->dashboardData['progress']['in_progress'] += (int)$status['total'];
        }
    }

    /**
     * @param array $stockItems
     *
     * @return void
     */
    private function setStockItemsInData(array $stockItems = [])
    {
        foreach ($stockItems as $item) {
            $date = $item['historyDate'];
            $dateFormatted = (new \DateTime($date))->format('d/m/Y');
            $readyForInvoiceAt = $item['ready_for_invoice_at']
                ? (new \DateTime($item['ready_for_invoice_at']))->format('d/m/Y')
                : null;
            $assignedAt = $item['assigned_at']
                ? (new \DateTime($item['assigned_at']))->format('d/m/Y')
                : null;
            $diffDays = (new \DateTime())->diff(new \DateTime($item['ready_for_invoice_at']))->days;
            $status = $this->resolveOrderStatusByDiffDays($diffDays);

            $key = sprintf('%s-%s', $date, $item['provider']);

            if (empty($this->dashboardData['stockItem'][$item['status']][$key])) {
                $this->dashboardData['stockItem'][$item['status']][$key] = [
                    'date'         => $date,
                    'formatted'    => $dateFormatted,
                    'provider_key' => strtolower($item['provider']),
                    'provider'     => $item['provider_name'],
                ];
            }

            $this->dashboardData['stockItem'][$item['status']][$key]['orders'][] = [
                'increment_id'         => $item['increment_id'],
                'zed_order'            => $item['zed_order'],
                'picking_list'         => $item['picking_list'],
                'operator_name'        => $item['operator_name'],
                'ready_for_invoice_at' => $readyForInvoiceAt,
                'assigned_at'          => $assignedAt,
                'status'               => $status
            ];
        }
    }

    /**
     * @param $diffDays
     * @return string
     */
    private function resolveOrderStatusByDiffDays($diffDays)
    {
        if ($diffDays < self::LATE_DAYS) {
            return 'success';
        }

        if ($diffDays === self::LATE_DAYS) {
            return 'warning';
        }

        return 'danger';
    }

    /**
     * @return void
     */
    private function setValueInData($item)
    {
        list($provider, $historyDate, $total, $status, $monoSku) = [
            strtolower($item['provider']),
            $item['historyDate'],
            (int)$item['total'],
            $item['status'],
            (int)$item['monoSku'],
        ];

        if (!in_array(strtolower($provider), $this->logisticsProvidersConcern->logisticsProvider())) {
            return;
        }

        $this->dashboardData['statuses'][$status]['dates'][$historyDate]['providers'][$provider] = $total;
        $this->dashboardData['totals'][$status] += $total;

        if ($status === EnumStockItemStatusType::STATUS_ASSIGNED) {
            $this->dashboardData['monoSku']['dates'][$historyDate]['providers'][$provider] = $monoSku;
            $this->dashboardData['totals']['monoSku'] += $monoSku;
        }
    }

    /**
     * @return array
     */
    private function oldDays()
    {
        $dates = [];

        for ($i = 0; $i < DashboardHandler::NUM_OLD_DAYS; $i++) {
            $dates[] = date('Y-m-d', strtotime('-' . $i . ' days'));
        }

        return $dates;
    }
}
