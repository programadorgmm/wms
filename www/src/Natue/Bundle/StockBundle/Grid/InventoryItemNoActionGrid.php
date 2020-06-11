<?php

namespace Natue\Bundle\StockBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\GridAbstract;

class InventoryItemNoActionGrid extends GridAbstract
{
    /**
     * @return void
     */
    public function setupGrid()
    {
        $translator = $this->container->get('translator');

        $this->addColumn($translator->trans('Name'))
            ->setField('productName')
            ->setIndex('inventoryItem.name');

        $this->addColumn($translator->trans('Barcode'))
            ->setField('barcode')
            ->setIndex('inventoryItem.name');

        $this->addColumn($translator->trans('Quantity'))
            ->setField('qty')
            ->setIndex('inventoryItem.name');

        $this->addColumn($translator->trans('Status'))
            ->setField('inventoryItemStatus')
            ->setIndex('inventoryItemStatus');
    }
}
