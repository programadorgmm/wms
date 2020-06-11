<?php

namespace Natue\Bundle\StockBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\GridAbstract;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType;

/**
 * Purchase Order Grid
 */
class PurchaseOrderItemGrid extends GridAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setupGrid()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->container->get('translator');
        $statuses   = EnumPurchaseOrderItemStatusType::$values;

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

        $this->addColumn($translator->trans('Cost'))
            ->setField('cost')
            ->setIndex('purchaseOrderItem.cost')
            ->setRenderType('Natue\Bundle\StockBundle\Grid\Render\FloatRender');

        $this->addColumn($translator->trans('Qty'))
            ->setField('qty')
            ->setIndex('qty')
            ->setFilterType('number_range')
            ->getFilter()
            ->setOperatorType('having_number_range');

        $this->addColumn($translator->trans('Invoice Cost'))
            ->setField('invoiceCost')
            ->setIndex('purchaseOrderItem.invoiceCost')
            ->setRenderType('Natue\Bundle\StockBundle\Grid\Render\FloatRender');

        $this->addColumn($translator->trans('ICMS'))
            ->setField('icms')
            ->setIndex('purchaseOrderItem.icms')
            ->setRenderType('Natue\Bundle\StockBundle\Grid\Render\FloatRender');

        $this->addColumn($translator->trans('ICMS ST'))
            ->setField('icmsSt')
            ->setIndex('purchaseOrderItem.icmsSt')
            ->setRenderType('Natue\Bundle\StockBundle\Grid\Render\FloatRender');

        $this->addColumn($translator->trans('Status'))
            ->setIndex('purchaseOrderItem.status')
            ->setField('status')
            ->setFilterType('select')
            ->getFilter()
            ->setOptions($options);

        if ($this->container->get('security.context')->isGranted(
            ['ROLE_ADMIN', 'ROLE_STOCK_PURCHASE_ORDER_ITEM_UPDATE', 'ROLE_STOCK_PURCHASE_ORDER_ITEM_DELETE']
        )
        ) {
            $this->addColumn($translator->trans('Action'))
                ->setTwig('NatueStockBundle:PurchaseOrderItem:gridAction.html.twig')
                ->setFilterType(false);
        }
    }
}
