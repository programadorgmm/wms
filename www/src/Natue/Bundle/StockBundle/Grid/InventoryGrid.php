<?php

namespace Natue\Bundle\StockBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\GridAbstract;

class InventoryGrid extends GridAbstract
{
    public function setupGrid()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->container->get('translator');

        $this->addColumn($translator->trans('Id'))
            ->setField('id')
            ->setIndex('inventory.id');

        $this->addColumn($translator->trans('Position name'))
            ->setField('stockPositionName')
            ->setIndex('stockPositionName');

        $this->addColumn($translator->trans('User'))
            ->setField('userName')
            ->setIndex('userName');

        $this->addColumn($translator->trans('Finished at'))
            ->setField('finishedAt')
            ->setIndex('inventoryItem.finishedAt')
            ->setFilterType('date_range')
            ->setRenderType('date_time');

        $this->addColumn($translator->trans('Action'))
            ->setTwig('NatueStockBundle:Inventory:listItemsGridAction.html.twig')
            ->setFilterType(false);
    }
}
