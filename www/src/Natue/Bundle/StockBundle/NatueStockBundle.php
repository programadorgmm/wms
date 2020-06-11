<?php

namespace Natue\Bundle\StockBundle;

use Natue\Bundle\CoreBundle\AbstractBundle;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumInventoryItemStatusType;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;

/**
 * Stock Bundle
 */
class NatueStockBundle extends AbstractBundle
{
    /**
     * Initial load
     *
     * @return void
     */
    public function boot()
    {
        $this->loadCustomTypes([
            EnumPurchaseOrderItemStatusType::class => EnumPurchaseOrderItemStatusType::ENUM_NAME,
            EnumInventoryItemStatusType::class => EnumInventoryItemStatusType::ENUM_NAME,
            EnumStockItemStatusType::class => EnumStockItemStatusType::ENUM_NAME,
        ]);

        parent::boot();
    }
}
