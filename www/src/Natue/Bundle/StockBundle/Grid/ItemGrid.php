<?php

namespace Natue\Bundle\StockBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\GridAbstract;

use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;

/**
 * Item Grid
 */
class ItemGrid extends GridAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setupGrid()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->container->get('translator');
        $statuses   = EnumStockItemStatusType::$values;

        $options = [];

        foreach ($statuses as $status) {
            $options[$status] = $status;
        }

        $this->addColumn($translator->trans('SKU'))
            ->setField('productSku')
            ->setIndex('zedProduct.sku');

        $this->addColumn($translator->trans('Name'))
            ->setField('productName')
            ->setIndex('zedProduct.name');

        $this->addColumn($translator->trans('Barcode'))
            ->setField('barcode')
            ->setIndex('stockItem.barcode');

        $this->addColumn($translator->trans('Expiration'))
            ->setField('dateExpiration')
            ->setIndex('stockItem.dateExpiration')
            ->setFilterType('date_range')
            ->setRenderType('date');

        $this->addColumn($translator->trans('Qty'))
            ->setField('qty')
            ->setIndex('qty')
            ->setFilterType('number_range')
            ->getFilter()
            ->setOperatorType('having_number_range');

        $this->addColumn($translator->trans('Status'))
            ->setField('statusName')
            ->setIndex('stockItem.status')
            ->setFilterType('select')
            ->getFilter()
            ->setOptions($options);

        $this->addColumn($translator->trans('Position'))
            ->setField('stockPositionName')
            ->setIndex('stockPosition.name');

        $this->addColumn($translator->trans('Pickable'))
            ->setField('pickable')
            ->setIndex('stockPosition.pickable')
            ->setFilterType('select')
            ->getFilter()
            ->setOptions([
                true  => 'true',
                false => 'false'
            ]);

        if ($this->container->get('security.context')->isGranted(
            ['ROLE_ADMIN', 'ROLE_STOCK_ITEM_UPDATE']
        )) {
            $this->addColumn($translator->trans('Action'))
                ->setField('action')
                ->setTwig('NatueStockBundle:Item:gridAction.html.twig')
                ->setFilterType(false);
        }
    }
}
