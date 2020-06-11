<?php

namespace Natue\Bundle\ShippingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LogisticProviderAndPackage extends AbstractType
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
                'logistics_provider_id',
                'zenstruck_ajax_entity',
                [
                    'label'          => 'Logistics Provider',
                    'class'          => 'NatueShippingBundle:ShippingLogisticsProvider',
                    'use_controller' => true,
                    'property'       => 'nameOfficial',
                    'required'       => true,
                ]
            )
            ->add(
                'package_id',
                'zenstruck_ajax_entity',
                [
                    'label'          => 'Scan Package Barcode',
                    'class'          => 'NatueShippingBundle:ShippingPackage',
                    'use_controller' => true,
                    'property'       => 'name',
                    'required'       => true
                ]
            )
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
            )
            ->add(
                'order_recheck',
                'hidden',
                [
                    'required'   => false,
                ]
            )
            ->add(
                'shipping_track_code',
                'text',
                [
                    'label'      => 'Shipping Barcode',
                    'required'   => true,
                    'widget_addon_prepend' => [
                        'icon' => 'barcode'
                    ],
                    'help_block' => ''
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
        return 'natue_shippingbundle_packing_logistic_provider_and_package_form';
    }
}
