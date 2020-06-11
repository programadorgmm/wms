<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PurchaseOrderItemCostAverageUpdate extends AbstractType
{
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
                    'disabled'       => true,
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
            );
    }

    public function getName()
    {
        return 'natue_stockbundle_purchase_order_item_cost_average_update';
    }
}
