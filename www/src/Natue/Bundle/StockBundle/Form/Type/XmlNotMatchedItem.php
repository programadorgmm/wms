<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


class XmlNotMatchedItem extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'zed_product',
                'zenstruck_ajax_entity',
                [
                    'label' => 'SKU',
                    'class' => 'NatueZedBundle:ZedProduct',
                    'property' => 'sku',
                    'use_controller' => true
                ]
            )
            ->add(
                'quantity',
                'integer',
                [
                    'widget_form_group_attr' => [
                        'class' => 'form-group',
                        'style' => 'width: 13%; margin: 0.5em',
                    ],
                    'attr' => [
                        'style' => 'width: 100%',
                    ],
                    'label' => 'Qty',
                ]
            )
            ->add(
                'xmlDescription',
                'text',
                [
                    'read_only' => true,
                    'label' => 'XML Desc',
                    'widget_form_group_attr' => [
                        'class' => 'form-group',
                        'style' => 'width: 50%; margin: 0.5em',
                    ],
                    'attr' => [
                        'style' => 'width: 100%',
                    ],
                ]
            )
            ->add(
                'xmlQuantity',
                'text',
                [
                    'read_only' => true,
                    'label' => 'XML Qty',
                    'widget_form_group_attr' => [
                        'class' => 'form-group',
                        'style' => 'width: 13%; margin: 0.5em',
                    ],
                    'attr' => [
                        'style' => 'width: 100%',
                    ],
                ]
            )
            ->add('xmlCode', 'hidden');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Natue\Bundle\StockBundle\Form\Model\XmlNotMatchedItem',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_stockbundle_xml_not_matched_item';
    }
}

