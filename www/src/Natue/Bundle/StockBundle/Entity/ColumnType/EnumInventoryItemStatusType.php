<?php

namespace Natue\Bundle\StockBundle\Entity\ColumnType;

use Natue\DBAL\EnumType;

class EnumInventoryItemStatusType extends EnumType
{
    const ENUM_NAME = 'EnumInventoryItemStatusType';

    const STATUS_NEW       = 'new';
    const STATUS_LOST      = 'lost';
    const STATUS_CONFIRMED = 'confirmed';

    public static $values = [
        self::STATUS_NEW,
        self::STATUS_LOST,
        self::STATUS_CONFIRMED
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
