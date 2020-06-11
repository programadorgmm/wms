<?php

namespace Natue\Bundle\UserBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\GridAbstract;

/**
 * User Grid
 */
class UserGrid extends GridAbstract
{
    /**
     * {@inheritdoc}
     */
    public function setupGrid()
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->container->get('translator');

        $this->addColumn($translator->trans('ID'))
            ->setField('id')
            ->setIndex('user.id')
            ->getFilter()
            ->getOperator()
            ->setComparisonType('equal');

        $this->addColumn($translator->trans('Username'))
            ->setField('username')
            ->setIndex('user.username');

        $this->addColumn($translator->trans('Name'))
            ->setField('name')
            ->setIndex('user.name');

        $this->addColumn($translator->trans('Email'))
            ->setField('email')
            ->setIndex('user.email');

        $this->addColumn($translator->trans('Enabled'))
            ->setField('enabled')
            ->setIndex('user.enabled')
            ->setRenderType('yes_no')
            ->setFilterType('yes_no');

        if ($this->container->get('security.context')->isGranted(
            ['ROLE_SUPER_ADMIN', 'ROLE_USER_USER_UPDATE', 'ROLE_USER_USER_DELETE']
        )
        ) {
            $this->addColumn($translator->trans('Action'))
                ->setTwig('NatueUserBundle:User:gridAction.html.twig')
                ->setFilterType(false);
        }
    }
}
