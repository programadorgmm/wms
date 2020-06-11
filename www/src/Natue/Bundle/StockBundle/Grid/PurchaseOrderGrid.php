<?php

namespace Natue\Bundle\StockBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\GridAbstract;

/**
 * Purchase Order Grid
 */
class PurchaseOrderGrid extends GridAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setupGrid()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->container->get('translator');

        $this->addColumn($translator->trans('ID'))
            ->setField('purchaseOrderId')
            ->setIndex('purchaseOrderId')
            ->getFilter()
            ->getOperator()
            ->setComparisonType('equal');

        $this->addColumn($translator->trans('Status'))
            ->setField('purchaseStatus')
            ->getFilter();

        $this->addColumn($translator->trans('Invoice Key'))
            ->setField('invoiceKey')
            ->setIndex('purchaseOrder.invoiceKey');

        $this->addColumn($translator->trans('Created At'))
            ->setField('createdAt')
            ->setIndex('purchaseOrder.createdAt')
            ->setFilterType('date_range')
            ->setRenderType('date');

        $this->addColumn($translator->trans('Delivered At'))
            ->setField('dateActualDelivery')
            ->setIndex('purchaseOrder.dateActualDelivery')
            ->setFilterType('date_range')
            ->setRenderType('date');

        $this->addColumn($translator->trans('Supplier'))
            ->setField('zedSupplierName')
            ->setIndex('zedSupplierName');

        $this->addColumn($translator->trans('Volumes Expected'))
            ->setField('volumesTotal')
            ->setIndex('purchaseOrder.volumesTotal')
            ->setFilterType('number_range')
            ->getFilter()
            ->setOperatorType('having_number_range');

        $this->addColumn($translator->trans('Volumes Received'))
            ->setField('volumesReceived')
            ->setIndex('purchaseOrder.volumesReceived')
            ->setFilterType('number_range')
            ->getFilter()
            ->setOperatorType('having_number_range');

        if ($this->container->get('security.context')->isGranted(
            [
                'ROLE_ADMIN',
                'ROLE_STOCK_PURCHASE_ORDER_ITEM_READ',
                'ROLE_STOCK_PURCHASE_ORDER_UPDATE',
                'ROLE_STOCK_PURCHASE_ORDER_DELETE'
            ]
        )
        ) {
            $this->addColumn($translator->trans('Action'))
                ->setTwig('NatueStockBundle:PurchaseOrder:gridAction.html.twig')
                ->setFilterType(false);
        }
    }
}
