<?php

namespace Natue\Bundle\ShippingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderIncrementIdLookup extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'increment_id',
                'text',
                [
                    'label'      => 'Scan Increment Id',
                    'required'   => true,
                    'widget_addon_prepend' => [
                        'icon' => 'barcode'
                    ],
                    'help_block' => 'Lookup for the related order of the picking list'
                ]
            );
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'natue_shippingbundle_picking_order_increment_id_lookup_form';
    }
}
