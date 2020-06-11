<?php
/**
 * Created by PhpStorm.
 * User: artur-natue
 * Date: 30/09/2014
 * Time: 12:04
 */

namespace Natue\Bundle\ShippingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NumeralPickingOrders extends AbstractType
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
                'mono_sku',
                'checkbox',
                [
                    'label'    => 'Mono SKU',
                    'required' => false,
                ]
            )
            ->add(
                'orders_amount',
                'integer',
                [
                    'label'      => 'Orders Amount',
                    'required'   => false,
                ]
            )
            ->add(
                'logistics_provider',
                'zenstruck_ajax_entity',
                [
                    'label'          => 'Logistics Provider',
                    'class'          => 'NatueShippingBundle:ShippingLogisticsProvider',
                    'use_controller' => true,
                    'property'       => 'nameOfficial',
                    'required'       => true
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
        return 'natue_shippingbundle_picking_numeral_orders_form';
    }
}
