<?php

namespace Natue\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * Form for Group
 */
class Group extends AbstractType
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * Construct
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Natue\Bundle\UserBundle\Service\Roles $roles */
        $roles = $this->container->get('natue.roles');

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
                'roles',
                'choice',
                [
                    'label'    => 'Roles',
                    'multiple' => true,
                    'required' => true,
                    'choices'  => $roles->getFormatedRoles()
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_userbundle_group';
    }
}
