<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form for Purchase Order
 */
class PurchaseOrder extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'invoice_key',
                'text',
                [
                    'label'    => 'Invoice Key',
                    'required' => true
                ]
            )

            ->add(
                'volumes_total',
                'integer',
                [
                    'label'    => 'Total of Volumes',
                    'required' => true
                ]
            )

            ->add(
                'cost_total',
                'integer',
                [
                    'label'                => 'Total Cost',
                    'required'             => true,
                    'help_block'           => 'In cents, for example 1010 is $10.10',
                    'widget_addon_prepend' => [
                        'icon' => 'usd'
                    ]
                ]
            )

            ->add(
                'zed_supplier',
                'zenstruck_ajax_entity',
                [
                    'label'          => 'Supplier',
                    'class'          => 'NatueZedBundle:ZedSupplier',
                    'use_controller' => true,
                    'property'       => 'name',
                    'required'       => false
                ]
            )
            ->add(
                'date_actual_delivery',
                'date',
                [
                    'label'    => 'Actual Delivery Date',
                    'required' => false,
                    'widget'   => 'single_text'
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_stockbundle_purchase_order';
    }
}
