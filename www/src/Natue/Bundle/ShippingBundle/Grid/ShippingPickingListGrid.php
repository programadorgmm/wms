<?php

namespace Natue\Bundle\ShippingBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\GridAbstract;

/**
 * Item Grid
 */
class ShippingPickingListGrid extends GridAbstract
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
            ->setIndex('shippingPickingList.id')
            ->getFilter()
            ->getOperator()
            ->setComparisonType('equal');

        $this->addColumn($translator->trans('Created At'))
            ->setField('createdAt')
            ->setIndex('shippingPickingList.createdAt')
            ->setFilterType('date_range')
            ->setRenderType(\Natue\Bundle\ShippingBundle\Grid\DateRender::class);

        $this->addColumn($translator->trans('Operator'))
            ->setField('userName')
            ->setIndex('user.name')
            ->getFilter()
            ->getOperator()
            ->setComparisonType('equal');

        $this->addColumn($translator->trans('Action'))
            ->setTwig('NatueShippingBundle:Picking:gridAction.html.twig')
            ->setFilterType(false);
    }
}
