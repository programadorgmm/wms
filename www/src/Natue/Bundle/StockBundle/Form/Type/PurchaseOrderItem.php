<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form for Purchase Order Item
 */
class PurchaseOrderItem extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'zed_product',
                'zenstruck_ajax_entity',
                [
                    'label'          => 'SKU',
                    'class'          => 'NatueZedBundle:ZedProduct',
                    'property'       => 'sku',
                    'disabled'       => $options['isZedProductDisabled'],
                    'use_controller' => true
                ]
            )
            ->add(
                'cost',
                'integer',
                [
                    'label'                => 'Cost',
                    'required'             => true,
                    'help_block'           => 'In cents, for example 1010 is $10.10',
                    'widget_addon_prepend' => [
                        'icon' => 'usd'
                    ]
                ]
            )
            ->add(
                'icms_st',
                'integer',
                [
                    'label'                => 'Unit Icms St',
                    'required'             => true,
                    'help_block'           => 'In cents, for example 1010 is $10.10',
                    'widget_addon_prepend' => [
                        'icon' => 'usd'
                    ]
                ]
            )
            ->add(
                'icms',
                'integer',
                [
                    'label'                => 'Unit Icms',
                    'required'             => true,
                    'help_block'           => 'In cents, for example 1010 is $10.10',
                    'widget_addon_prepend' => [
                        'icon' => 'usd'
                    ]
                ]
            )
            ->add(
                'invoice_cost',
                'integer',
                [
                    'label'                => 'Unit Invoice Cost',
                    'required'             => true,
                    'help_block'           => 'In cents, for example 1010 is $10.10',
                    'widget_addon_prepend' => [
                        'icon' => 'usd'
                    ]
                ]
            )
            ->add(
                'quantity',
                'integer',
                [
                    'label'    => 'Quantity',
                    'required' => true
                ]
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     *
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'isZedProductDisabled' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_stockbundle_purchase_order_item';
    }
}
