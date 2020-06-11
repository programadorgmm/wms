<?php

namespace Natue\Bundle\ShippingBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityRepository;

use Natue\Bundle\ZedBundle\Entity\ZedOrder;

class AddPickingObservationConferenceSubscriber implements EventSubscriberInterface
{
    private $zedOrder;
    private $readStatus;
    private $options;

    public function __construct(ZedOrder $order, $readStatus, array $options)
    {
        $this->zedOrder = $order;
        $this->readStatus = (bool)$readStatus;
        $this->options = $options;
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();

        $formOptions = [
            'class'         => 'Natue\Bundle\ZedBundle\Entity\ZedOrder',
            'property'      => 'pickingObservation',
            'choices'       => [$this->zedOrder],
            'required'      => true,
            'expanded'      => true,
            'multiple'      => false,
            'label'         => 'Picking Observation',
            'help_block'    => 'Read carefully before proceed',
            'data'          => ((!$this->readStatus) ?: $this->zedOrder),
        ];

        $form->add('pickingObservation', 'entity', $formOptions);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Natue\Bundle\ZedBundle\Entity\ZedOrder'
        ]);
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::PRE_SET_DATA => 'preSetData'];
    }
}
