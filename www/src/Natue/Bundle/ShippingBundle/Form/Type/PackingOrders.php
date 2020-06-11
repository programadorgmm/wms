<?php

namespace Natue\Bundle\ShippingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PackingOrders extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'order_increment_id',
                'text',
                [
                    'label'      => 'Order Barcode',
                    'required'   => true,
                    'widget_addon_prepend' => [
                        'icon' => 'barcode'
                    ],
                    'help_block' => 'Order barcode starts with BR and followed by numbers'
                ]
            );
    }

    /**
     * @return string The name of this type
     */
    public function getName()
    {
        return 'natue_shippingbundle_packing_orders_form';
    }
}
