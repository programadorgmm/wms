<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrderRequestType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description','text',
                [
                    'label'    => 'Description',
                    'required' => true,
                ]
            )
            ->add(
                'supplier',
                'zenstruck_ajax_entity',
                [
                    'label'          => 'Supplier',
                    'class'          => 'NatueZedBundle:ZedSupplier',
                    'use_controller' => true,
                    'property'       => 'name',
                    'required'       => false
                ]
            ) ->add(
                'submitFile',
                'file',
                [
                    'label'    => 'Items CSV',
                    'required' => true
                ]
            );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'natue_bundle_stockbundle_orderrequest';
    }
}
