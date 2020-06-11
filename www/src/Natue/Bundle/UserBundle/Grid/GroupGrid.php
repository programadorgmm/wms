<?php

namespace Natue\Bundle\UserBundle\Grid;

use PedroTeixeira\Bundle\GridBundle\Grid\GridAbstract;

/**
 * Groups Grid
 */
class GroupGrid extends GridAbstract
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
            ->setIndex('groupEntity.id')
            ->getFilter()
            ->getOperator()
            ->setComparisonType('equal');

        $this->addColumn($translator->trans('Name'))
            ->setField('name')
            ->setIndex('groupEntity.name');

        if ($this->container->get('security.context')->isGranted(
            ['ROLE_SUPER_ADMIN', 'ROLE_USER_GROUP_UPDATE', 'ROLE_USER_GROUP_DELETE']
        )
        ) {
            $this->addColumn($translator->trans('Action'))
                ->setTwig('NatueUserBundle:Group:gridAction.html.twig')
                ->setFilterType(false);
        }
    }
}
