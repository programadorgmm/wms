<?php

namespace Natue\Bundle\StockBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\GridAbstract;

use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;

class OrderStockItemsGrid extends GridAbstract
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

        $this->addColumn($translator->trans('#'))
            ->setField('stockItemId')
            ->setIndex('stockItem.id');

        $this->addColumn($translator->trans('SKU'))
            ->setField('productSku')
            ->setIndex('zedProduct.sku');

        $this->addColumn($translator->trans('Name'))
            ->setField('productName')
            ->setIndex('zedProduct.name');

        $this->addColumn($translator->trans('Barcode'))
            ->setField('barcode')
            ->setIndex('stockItem.barcode');

        $this->addColumn($translator->trans('Expiration Date'))
            ->setField('dateExpiration')
            ->setIndex('stockItem.dateExpiration')
            ->setFilterType('date_range')
            ->setRenderType('date');

        $this->addColumn($translator->trans('Status'))
            ->setField('stockItemStatus')
            ->setIndex('stockItem.status')
            ->setFilterType('select')
            ->getFilter()
            ->setOptions($options);

        $this->addColumn($translator->trans('Position'))
            ->setField('positionName')
            ->setIndex('stockPosition.name');

        if ($this->container->get('security.context')->isGranted(
            ['ROLE_ADMIN', 'ROLE_STOCK_ORDER_RETURN']
        )
        ) {
            $this->addColumn($translator->trans('Action'))
                ->setTwig('NatueStockBundle:OrderReturn:itemsListGridAction.html.twig')
                ->setFilterType(false);
        }
    }
}
