<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType as StatusTransition;

class ProductToPosition extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $validTransitions = [
            StatusTransition::STATUS_READY,
            StatusTransition::STATUS_DAMAGED,
            StatusTransition::STATUS_EXPIRED
        ];

        $builder
            ->add(
                'stock_item_barcode',
                'text',
                [
                    'label'    => 'Stock Item Barcode',
                    'required' => true,
                    'disabled' => true,
                ]
            )
            ->add(
                'position',
                'zenstruck_ajax_entity',
                [
                    'label'          => 'Position',
                    'class'          => 'NatueStockBundle:StockPosition',
                    'property'       => 'name',
                    'use_controller' => true,
                    'required'       => true,
                    'widget_addon_prepend' => [
                        'icon' => 'barcode'
                    ],
                ]
            )
            ->add(
                'transition',
                'choice',
                [
                    'label'    => 'Status transition',
                    'required' => true,
                    'choices'  => array_combine($validTransitions, $validTransitions),
                ]
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_stockbundle_product_to_position';
    }
}
