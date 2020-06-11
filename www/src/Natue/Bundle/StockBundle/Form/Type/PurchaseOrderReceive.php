<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form for Purchase Order Receive
 */
class PurchaseOrderReceive extends AbstractType
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
                'purchase_order_reference',
                'text',
                [
                    'label'      => 'Select Purchase Order',
                    'required'   => true,
                    'widget_addon_prepend' => [
                        'icon' => 'barcode'
                    ],
                    'help_block' => 'Scan barcode to input Invoice Key or Purchase Order Id',
                ]
            )
            ->add(
                'volumes',
                'integer',
                [
                    'label'    => 'Volumes amount',
                    'required' => true,
                ]
            );
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'natue_stockbundle_purchase_order_receive';
    }
}
