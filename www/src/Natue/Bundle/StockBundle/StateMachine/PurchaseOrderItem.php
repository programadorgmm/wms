<?php

namespace Natue\Bundle\StockBundle\StateMachine;

use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType as Status;

/**
 * State machine PurchaseOrderItem
 */
class PurchaseOrderItem extends StateMachineAbstract
{
    const INITIAL_STATUS = Status::STATUS_INCOMING;

    /**
     * Get class name
     *
     * @return string
     */
    protected function getClassName()
    {
        return 'PurchaseOrderItem';
    }

    /**
     * Get array with possible statues
     *
     * @return array
     */
    protected function getStates()
    {
        return [
            Status::STATUS_INCOMING  => [
                'type' => self::STATE_TYPE_INITIAL,
            ],
            Status::STATUS_RECEIVING => [
                'type' => self::STATE_TYPE_NORMAL,
            ],
            Status::STATUS_RECEIVED  => [
                'type' => self::STATE_TYPE_FINAL,
            ],
            Status::STATUS_DELETED  => [
                'type' => self::STATE_TYPE_FINAL,
            ]
        ];
    }

    /**
     * Get array with possible transitions
     *
     * @return array
     */
    protected function getTransitions()
    {
        return [
            Status::STATUS_INCOMING => [
                'from' => [
                    Status::STATUS_RECEIVING,
                ],
            ],
            Status::STATUS_RECEIVING => [
                'from' => [
                    Status::STATUS_INCOMING,
                ],
            ],
            Status::STATUS_RECEIVED => [
                'from' => [
                    Status::STATUS_RECEIVING
                ],
            ],
            Status::STATUS_DELETED => [
                'from' => [
                    Status::STATUS_INCOMING,
                    Status::STATUS_RECEIVING,
                    Status::STATUS_RECEIVED
                ],
            ],
        ];
    }
}
