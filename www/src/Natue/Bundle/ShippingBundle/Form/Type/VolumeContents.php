<?php

namespace Natue\Bundle\ShippingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class VolumeContents extends AbstractType
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
                'tracking_code',
                'text',
                [
                    'label'    => 'Tracking Code',
                    'required' => true,
                ]
            )
            ->add(
                'package_id',
                'choice',
                [
                    'label'    => 'Package',
                    'choices'  => [
                        $options['data']['package_id'] => $options['data']['package_name'],
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'order_increment_id',
                'choice',
                [
                    'label'    => 'Order Increment Id',
                    'choices'  => [
                        $options['data']['order_increment_id'] => $options['data']['order_increment_id'],
                    ],
                    'required' => true,
                ]
            );

        // Dynamic number of input fields
        if (isset($options['data']['items']) && is_array($options['data']['items'])) {
            foreach ($options['data']['items'] as $item) {
                $builder->add(
                    sprintf("item_qty:%s", $item['zed_product_id']),
                    'integer',
                    [
                        'label'      => 'SKU: ' . $item['sku'],
                        'required'   => true,
                        'data'       => 0,
                        'help_block' => 'Items left: ' . $item['amount_left'],
                    ]
                );
            }
        }
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'natue_shippingbundle_packing_volume_contents_form';
    }
}
