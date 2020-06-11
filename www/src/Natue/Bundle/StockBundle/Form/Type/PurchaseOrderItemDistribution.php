<?php

namespace Natue\Bundle\StockBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

use Natue\Bundle\StockBundle\Form\Model\PurchaseOrderItemDistribution as PurchaseOrderItemDistributionEntity;

/**
 * Form for Purchase Order Item Distribution
 */
class PurchaseOrderItemDistribution extends AbstractType
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
                    'label'    => 'Barcode',
                    'required' => true,
                    'widget_addon_prepend' => [
                        'icon' => 'barcode'
                    ]
                ]
            )
            ->add(
                'date_expiration',
                'date',
                [
                    'label'    => 'Date Expiration',
                    'required' => true,
                    'widget'   => 'single_text',
                ]
            )
            ->add(
                'quantity',
                'integer',
                [
                    'label'    => 'Quantity',
                    'required' => true,
                ]
            )
            ->add(
                'position',
                'zenstruck_ajax_entity',
                [
                    'label'          => 'Position',
                    'class'          => 'NatueStockBundle:StockPosition',
                    'property'       => 'name',
                    'use_controller' => true,
                    'required'       => false
                ]
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     *
     * @return void
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'isZedProductDisabled' => false,
                'isPositionDisabled'   => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_stockbundle_purchase_order_item';
    }
}
