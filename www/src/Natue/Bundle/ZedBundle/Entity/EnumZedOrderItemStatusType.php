<?php

namespace Natue\Bundle\ZedBundle\Entity;

use Natue\DBAL\EnumType;

class EnumZedOrderItemStatusType extends EnumType
{
    const ENUM_NAME = 'EnumZedOrderItemStatusType';

    const STATUS_NEW                           = 'new';
    const STATUS_CANCELED                      = 'canceled';
    const STATUS_READY_FOR_PICKING             = 'ready_for_picking';
    const STATUS_READY_FOR_INVOICE             = 'ready_for_invoice';
    const STATUS_INVOICE_CREATED               = 'invoice_created';
    const STATUS_INVOICE_CREATION_INITIALIZED  = 'invoice_creation_initialized';
    const STATUS_CLARIFY_PICKING_FAILED        = 'clarify_picking_failed';
    const STATUS_DELIVERY_FAIL                 = 'delivery_fail';
    const STATUS_CLOSED                        = 'closed';
    const STATUS_SHIPPED                       = 'shipped';
    const STATUS_WAITING_FOR_SHIPPING          = 'waiting_for_shipping';

    public static $values = [
        self::STATUS_NEW,
        self::STATUS_CANCELED,
        self::STATUS_READY_FOR_PICKING,
        self::STATUS_READY_FOR_INVOICE,
        self::STATUS_INVOICE_CREATED,
        self::STATUS_INVOICE_CREATION_INITIALIZED,
        self::STATUS_CLARIFY_PICKING_FAILED,
        self::STATUS_DELIVERY_FAIL,
        self::STATUS_CLOSED,
        self::STATUS_SHIPPED,
        self::STATUS_WAITING_FOR_SHIPPING,
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
