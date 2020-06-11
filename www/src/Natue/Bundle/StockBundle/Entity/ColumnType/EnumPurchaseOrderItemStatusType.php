<?php

namespace Natue\Bundle\StockBundle\Entity\ColumnType;

use Natue\DBAL\EnumType;

class EnumPurchaseOrderItemStatusType extends EnumType
{
    const ENUM_NAME = 'EnumPurchaseOrderItemStatusType';

    const STATUS_INCOMING  = 'incoming';
    const STATUS_RECEIVING = 'receiving';
    const STATUS_RECEIVED  = 'received';
    const STATUS_DELETED   = 'deleted';

    public static $values = [
        self::STATUS_INCOMING,
        self::STATUS_RECEIVING,
        self::STATUS_RECEIVED,
        self::STATUS_DELETED
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
