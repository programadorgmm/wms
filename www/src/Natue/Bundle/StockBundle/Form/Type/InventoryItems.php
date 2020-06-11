<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class InventoryItems extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'barcode',
                'text',
                [
                    'label'          => 'Inventory item barcode',
                    'required'       => true
                ]
            )
            ->add(
                'quantity',
                'integer',
                [
                    'label'          => 'Inventory item quantity',
                    'required'       => true
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_inventory_items';
    }
}
