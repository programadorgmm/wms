<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form for StockItem, action: moveFromPositionAction
 */
class StockItemMoveFromItem extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'sku',
                'text',
                [
                    'label'     => 'SKU',
                    'required'  => true,
                    'read_only' => true,
                ]
            )
            ->add(
                'barcode',
                'hidden',
                [
                    'required' => true,
                ]
            )
            ->add(
                'dateExpiration',
                'hidden',
                [
                    'required' => true,
                ]
            )
            ->add(
                'old_stock_position_id',
                'text',
                [
                    'label'     => 'From position',
                    'required'  => true,
                    'read_only' => true,
                    'attr' => [
                        'class' => 'form-control'
                    ],
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
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_stockbundle_position_move_from_item';
    }
}
