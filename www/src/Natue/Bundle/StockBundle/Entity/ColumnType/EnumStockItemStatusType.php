<?php

namespace Natue\Bundle\StockBundle\Entity\ColumnType;

use Natue\DBAL\EnumType;

class EnumStockItemStatusType extends EnumType
{
    const ENUM_NAME = 'EnumStockItemStatusType';

    const STATUS_INCOMING            = 'incoming';
    const STATUS_READY               = 'ready';
    const STATUS_ASSIGNED            = 'assigned';
    const STATUS_WAITING_FOR_PICKING = 'waiting_for_picking';
    const STATUS_PICKED              = 'picked';
    const STATUS_SOLD                = 'sold';
    const STATUS_RETURNED            = 'returned';
    const STATUS_DAMAGED             = 'damaged';
    const STATUS_LOST                = 'lost';
    const STATUS_EXPIRED             = 'expired';
    const STATUS_READY_FOR_SHIPPING  = 'ready_for_shipping';

    public static $values = [
        self::STATUS_INCOMING,
        self::STATUS_READY,
        self::STATUS_ASSIGNED,
        self::STATUS_WAITING_FOR_PICKING,
        self::STATUS_PICKED,
        self::STATUS_SOLD,
        self::STATUS_DAMAGED,
        self::STATUS_RETURNED,
        self::STATUS_LOST,
        self::STATUS_EXPIRED,
        self::STATUS_READY_FOR_SHIPPING,
    ];

    /**
     * @return string
     */
    public function getName()
    {
        return self::ENUM_NAME;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return self::$values;
    }
}
