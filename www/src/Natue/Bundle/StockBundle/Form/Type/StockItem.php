<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form for Stock Item
 */
class StockItem extends AbstractType
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
                    'label'          => 'Barcode',
                    'required'       => true
                ]
            )
            ->add(
                'date_expiration',
                'date',
                [
                    'label'    => 'Expiration Date',
                    'required' => true,
                    'widget'   => 'single_text'
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_stockbundle_item';
    }
}
