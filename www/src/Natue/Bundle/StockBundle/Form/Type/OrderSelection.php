<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OrderSelection extends AbstractType
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
                    'help_block' => 'Lookup for the order to refund'
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_stockbundle_order_selection';
    }
}
