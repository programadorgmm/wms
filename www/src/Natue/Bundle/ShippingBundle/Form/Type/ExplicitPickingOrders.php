<?php

namespace Natue\Bundle\ShippingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form for Picking Orders
 */
class ExplicitPickingOrders extends AbstractType
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
                'orders_list',
                'text',
                [
                    'label'      => 'Order Increment IDs',
                    'required'   => true,
                    'help_block' => 'Comma separated'
                ]
            );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'natue_shippingbundle_picking_explicit_orders_form';
    }
}
