<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form for StockItem, action: moveAction
 */
class StockItemMove extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'old_stock_position_id',
                'zenstruck_ajax_entity',
                [
                    'label'          => 'From position',
                    'class'          => 'NatueStockBundle:StockPosition',
                    'property'       => 'name',
                    'use_controller' => true
                ]
            )
            ->add(
                'oldStockPositionCheck',
                'checkbox',
                [
                    'required' => false,
                    'label_attr' => ['class' => 'invisible']
                ]
            )
            ->add(
                'new_stock_position_id',
                'zenstruck_ajax_entity',
                [
                    'label'          => 'To position',
                    'class'          => 'NatueStockBundle:StockPosition',
                    'property'       => 'name',
                    'use_controller' => true,
                    'required'       => true
                ]

            )
            ->add(
                'new_stock_positionCheck',
                'checkbox',
                [
                    'required' => false,
                    'label_attr' => ['class' => 'invisible']
                ]
            )
            ->add(
                'quantity',
                'integer',
                [
                    'label'    => 'Quantity',
                    'required' => true
                ]
            )
            ->add(
                'quantityCheck',
                'checkbox',
                [
                    'required' => false,
                    'label_attr' => ['class' => 'invisible']
                ]
            )
            ->add(
                'barcode',
                'text',
                [
                    'label'          => 'Barcode',
                    'required'       => true
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_stockbundle_position_move';
    }
}
