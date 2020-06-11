<?php

namespace Natue\Bundle\ShippingBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Natue\Bundle\ShippingBundle\Form\EventListener\AddPickingObservationConferenceSubscriber;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;

class ProductBarcode extends AbstractType
{
    private $zedOrder;
    private $pickingReadStatus;

    public function __construct(ZedOrder $order, $pickingReadStatus)
    {
        $this->zedOrder = $order;
        $this->pickingReadStatus = (bool)$pickingReadStatus;
    }

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
                'code',
                'text',
                [
                    'label'      => 'Order Item',
                    'required'   => true,
                    'widget_addon_prepend' => [
                        'icon' => 'barcode'
                    ],
                    'help_block' => 'Scan barcode',
                ]
            );

        if ($this->zedOrder->getPickingObservation()) {
            $builder->addEventSubscriber(
                new AddPickingObservationConferenceSubscriber($this->zedOrder, $this->pickingReadStatus, $options)
            );
        }
    }

    /**
     * @return string The name of this type
     */
    public function getName()
    {
        return 'natue_shippingbundle_picking_order_productbarcode_form';
    }
}
