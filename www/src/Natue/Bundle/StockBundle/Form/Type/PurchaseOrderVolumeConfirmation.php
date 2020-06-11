<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form for Purchase Order Receive
 */
class PurchaseOrderVolumeConfirmation extends AbstractType
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
                'purchase_order_id',
                'hidden'
            );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'natue_stockbundle_purchase_order_volume_confirmation';
    }
}
