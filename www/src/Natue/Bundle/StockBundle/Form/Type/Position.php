<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form for Position
 */
class Position extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                [
                    'label'    => 'Name',
                    'required' => true
                ]
            )
            ->add(
                'sort',
                'integer',
                [
                    'label'    => 'Order',
                    'required' => true
                ]
            )
            ->add(
                'pickable',
                'choice',
                [
                    'label'    => 'Pickable',
                    'required' => true,
                    'choices'  => [
                        '0' => 'Disabled',
                        '1' => 'Enabled'
                    ]
                ]
            )
            ->add(
                'enabled',
                'choice',
                [
                    'label'    => 'Enabled',
                    'required' => true,
                    'choices'  => [
                        '0' => 'Disabled',
                        '1' => 'Enabled'
                    ]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_stockbundle_position';
    }
}
