<?php

namespace Natue\Bundle\StockBundle\StateMachine;

use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType as Status;

/**
 * State machine StockItem
 */
class StockItem extends StateMachineAbstract
{
    const INITIAL_STATUS = Status::STATUS_INCOMING;

    /**
     * @return string
     */
    protected function getClassName()
    {
        return 'StockItem';
    }

    /**
     * @return array
     */
    protected function getStates()
    {
        return [
            Status::STATUS_INCOMING => [
                'type' => self::STATE_TYPE_INITIAL,
            ],
            Status::STATUS_READY => [
                'type' => self::STATE_TYPE_NORMAL,
            ],
            Status::STATUS_ASSIGNED => [
                'type' => self::STATE_TYPE_NORMAL,
            ],
            Status::STATUS_WAITING_FOR_PICKING => [
                'type' => self::STATE_TYPE_NORMAL,
            ],
            Status::STATUS_PICKED => [
                'type' => self::STATE_TYPE_NORMAL,
            ],
            Status::STATUS_SOLD => [
                'type' => self::STATE_TYPE_NORMAL,
            ],
            Status::STATUS_READY_FOR_SHIPPING => [
                'type' => self::STATE_TYPE_NORMAL,
            ],
            Status::STATUS_RETURNED => [
                'type' => self::STATE_TYPE_NORMAL,
            ],
            Status::STATUS_LOST => [
                'type' => self::STATE_TYPE_NORMAL,
            ],
            Status::STATUS_DAMAGED => [
                'type' => self::STATE_TYPE_FINAL,
            ],
            Status::STATUS_EXPIRED => [
                'type' => self::STATE_TYPE_FINAL,
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getTransitions()
    {
        return [
            Status::STATUS_READY => [
                'from' => [
                    Status::STATUS_INCOMING,
                    Status::STATUS_ASSIGNED,
                    Status::STATUS_RETURNED,
                    Status::STATUS_PICKED,
                    Status::STATUS_WAITING_FOR_PICKING,
                ],
            ],
            Status::STATUS_ASSIGNED => [
                'from' => [
                    Status::STATUS_READY,
                ],
            ],
            Status::STATUS_WAITING_FOR_PICKING => [
                'from' => [
                    Status::STATUS_ASSIGNED,
                ],
            ],
            Status::STATUS_PICKED => [
                'from' => [
                    Status::STATUS_WAITING_FOR_PICKING,
                ],
            ],
            Status::STATUS_READY_FOR_SHIPPING => [
                'from' => [
                    Status::STATUS_PICKED,
                ],
            ],
            Status::STATUS_SOLD => [
                'from' => [
                    Status::STATUS_READY_FOR_SHIPPING,
                ],
            ],
            Status::STATUS_RETURNED => [
                'from' => [
                    Status::STATUS_SOLD,
                ],
            ],
            Status::STATUS_DAMAGED => [
                'from' => [
                    Status::STATUS_INCOMING,
                    Status::STATUS_READY,
                    Status::STATUS_ASSIGNED,
                    Status::STATUS_WAITING_FOR_PICKING,
                    Status::STATUS_PICKED,
                    Status::STATUS_RETURNED,
                ],
            ],
            Status::STATUS_LOST => [
                'from' => [
                    Status::STATUS_INCOMING,
                    Status::STATUS_READY,
                    Status::STATUS_ASSIGNED,
                    Status::STATUS_WAITING_FOR_PICKING,
                    Status::STATUS_PICKED,
                ],
            ],
            Status::STATUS_EXPIRED => [
                'from' => [
                    Status::STATUS_READY,
                    Status::STATUS_RETURNED,
                ],
            ],
        ];
    }
}
