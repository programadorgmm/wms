<?php

namespace Natue\Bundle\InvoiceBundle\Entity\ColumnType;

use Natue\DBAL\EnumType;

/**
 * Class EnumInvoiceStatusType
 * @package Natue\Bundle\InvoiceBundle\Entity\ColumnType
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class EnumInvoiceStatusType extends EnumType
{
    const STATUS_INITIALIZED = 'initialized';
    const STATUS_CREATED = 'created';

    /**
     * @return string
     */
    public function getName()
    {
        return 'EnumInvoiceStatusType';
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return [
            self::STATUS_INITIALIZED,
            self::STATUS_CREATED,
        ];
    }
}
