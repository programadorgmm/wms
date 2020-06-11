<?php

namespace Natue\Bundle\StockBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\GridAbstract;

/**
 * Position Grid
 */
class PositionGrid extends GridAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setupGrid()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->container->get('translator');

        $this->addColumn($translator->trans('ID'))
            ->setField('id')
            ->setIndex('stockPosition.id')
            ->getFilter()
            ->getOperator()
            ->setComparisonType('equal');

        $this->addColumn($translator->trans('Name'))
            ->setField('name')
            ->setIndex('stockPosition.name');

        $this->addColumn($translator->trans('Inventory'))
            ->setField('inventory')
            ->setIndex('stockPosition.inventory')
            ->setRenderType('yes_no')
            ->setFilterType('yes_no');

        $this->addColumn($translator->trans('Pickable'))
            ->setField('pickable')
            ->setIndex('stockPosition.pickable')
            ->setRenderType('yes_no')
            ->setFilterType('yes_no');

        $this->addColumn($translator->trans('Enabled'))
            ->setField('enabled')
            ->setIndex('stockPosition.enabled')
            ->setRenderType('yes_no')
            ->setFilterType('yes_no');

        $this->addColumn($translator->trans('Order'))
            ->setField('sort')
            ->setIndex('stockPosition.sort')
            ->getFilter()
            ->getOperator()
            ->setComparisonType('equal');

        if ($this->container->get('security.context')->isGranted(
            ['ROLE_ADMIN', 'ROLE_STOCK_POSITION_UPDATE', 'ROLE_STOCK_POSITION_DELETE']
        )
        ) {
            $this->addColumn($translator->trans('Action'))
                ->setTwig('NatueStockBundle:Position:gridAction.html.twig')
                ->setFilterType(false);
        }
    }
}
