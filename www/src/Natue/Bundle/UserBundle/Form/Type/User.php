<?php

namespace Natue\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form for User
 */
class User extends AbstractType
{
    /**
     * @var bool
     */
    private $isUpdate = false;

    /**
     * Construct
     *
     * @param bool $isUpdate
     */
    public function __construct($isUpdate = false)
    {
        $this->isUpdate = $isUpdate;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'username',
                'text',
                [
                    'label'    => 'Username',
                    'required' => true
                ]
            )

            ->add(
                'name',
                'text',
                [
                    'label'    => 'Name',
                    'required' => true
                ]
            )

            ->add(
                'email',
                'email',
                [
                    'label'    => 'Email',
                    'required' => true
                ]
            )

            ->add(
                'enabled',
                'choice',
                [
                    'label'    => 'Status',
                    'required' => true,
                    'choices'  => [
                        '0' => 'Disabled',
                        '1' => 'Enabled'
                    ]
                ]
            )

            ->add(
                'groups',
                'zenstruck_ajax_entity',
                [
                    'label'          => 'Groups',
                    'multiple'       => true,
                    'required'       => true,
                    'class'          => 'NatueUserBundle:Group',
                    'use_controller' => true,
                    'property'       => 'name'
                ]
            );

        if ($this->isUpdate) {
            $builder
                ->add(
                    'change_password',
                    'checkbox',
                    [
                        'label'    => 'Change Password?',
                        'required' => false,
                        'mapped'   => false
                    ]
                )

                ->add(
                    'plainPassword',
                    'repeated',
                    [
                        'type'           => 'password',
                        'first_options'  => ['label' => 'New Password'],
                        'second_options' => ['label' => 'New Password Confirmation'],
                        'required'       => false
                    ]
                );
        } else {
            $builder
                ->add(
                    'plainPassword',
                    'repeated',
                    [
                        'type'           => 'password',
                        'first_options'  => ['label' => 'Password'],
                        'second_options' => ['label' => 'Password Confirmation'],
                        'required'       => true
                    ]
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'natue_userbundle_user';
    }
}
