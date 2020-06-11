<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form for uploading Xml file for purchase order items
 */
class PurchaseOrderItemXml extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'orderRequest',
                'zenstruck_ajax_entity',
                [
                    'label' => 'Order Request',
                    'class' => 'NatueStockBundle:OrderRequest',
                    'use_controller' => true,
                    'repo_method' => 'searchByLongName',
                    'required' => false,
                    'property' => 'description'
                ]
            )
            ->add(
                'shippingCost',
                'money',
                [
                    'currency' => 'BRL',
                    'required' => false,
                ]
            )
            ->add(
                'submitFile',
                'file',
                [
                    'label' => 'Import XML',
                    'required' => true
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'widget_type' => 'inline'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_stockbundle_purchase_order_item_xml';
    }
}
