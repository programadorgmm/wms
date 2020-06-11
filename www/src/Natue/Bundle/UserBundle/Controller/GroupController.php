<?php

namespace Natue\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Natue\Bundle\UserBundle\Entity\Group;
use Natue\Bundle\UserBundle\Form\Type\Group as GroupForm;

/**
 * Group controller
 *
 * @Route("/group")
 */
class GroupController extends Controller
{
    /**
     * List action
     *
     * @Route("/list", name="user_group_list")
     * @Template()
     * @Secure(roles="ROLE_SUPER_ADMIN,ROLE_USER_GROUP_READ")
     *
     * @throws \Exception
     *
     * @return array
     */
    public function listAction()
    {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository   = $this->getDoctrine()->getRepository('NatueUserBundle:Group');
        $queryBuilder = $repository->createQueryBuilder('groupEntity');

        /** @var \Natue\Bundle\UserBundle\Grid\GroupGrid $grid */
        $grid = $this->get('pedroteixeira.grid')->createGrid('\Natue\Bundle\UserBundle\Grid\GroupGrid');
        $grid->setQueryBuilder($queryBuilder);

        if ($grid->isResponseAnswer()) {
            return $grid->render();
        }

        return [
            'grid' => $grid->render()
        ];
    }

    /**
     * Create action
     *
     * @Route("/create", name="user_group_create")
     * @Template()
     * @Secure(roles="ROLE_SUPER_ADMIN,ROLE_USER_GROUP_CREATE")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        $groupManager = $this->get('fos_user.group_manager');

        /* @var Group $group */
        $group = $groupManager->createGroup(null);
        $form  = $this->createForm(new GroupForm($this->container), $group);

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                if ($groupManager->findGroupByName($group->getName())) {
                    throw new \Exception('Group name already exist');
                }

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                $groupManager->updateGroup($group);
                $this->get('session')->getFlashBag()->add('success', 'Created');

                if ($this->get('security.context')->isGranted(['ROLE_SUPER_ADMIN', 'ROLE_USER_GROUP_UPDATE'])) {
                    $this->get('session')->getFlashBag()->add(
                        'info',
                        $this->get('translator')->trans(
                            'Do you want to edit this row? <a href="%url%">Click here</a>',
                            ['%url%' => $this->generateUrl('user_group_update', ['id' => $group->getId()])]
                        )
                    );
                }

                return $this->redirect($this->generateUrl('user_group_create'));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Update action
     *
     * @Route("/{id}/update", name="user_group_update")
     * @Template()
     * @Secure(roles="ROLE_SUPER_ADMIN,ROLE_USER_GROUP_UPDATE")
     *
     * @param int                                       $id entity id
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function updateAction($id, Request $request)
    {
        $groupManager = $this->get('fos_user.group_manager');

        /* @var Group $group */
        $group = $groupManager->findGroupBy(['id' => $id]);

        if (!$group) {
            throw $this->createNotFoundException('Not found');
        }

        $form = $this->createForm(new GroupForm($this->container), $group);

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                /* @var Group $groupDuplicated */
                $groupDuplicated = $groupManager->findGroupByName($group->getName());

                if ($groupDuplicated) {
                    if ($groupDuplicated->getId() != $group->getId()) {
                        throw new \Exception('Group name already exist');
                    }
                }

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                $groupManager->updateGroup($group);
                $this->get('session')->getFlashBag()->add('success', 'Updated');

                return $this->redirect($this->generateUrl('user_group_update', ['id' => $group->getId()]));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'group' => $group,
            'form'  => $form->createView(),
        ];
    }

    /**
     * Delete action
     *
     * @Route("/{id}/delete", name="user_group_delete")
     * @Template()
     * @Secure(roles="ROLE_SUPER_ADMIN,ROLE_USER_GROUP_DELETE")
     *
     * @param int $id entity id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function deleteAction($id)
    {
        $groupManager = $this->get('fos_user.group_manager');

        /* @var Group $group */
        $group = $groupManager->findGroupBy(['id' => $id]);

        if (!$group) {
            throw $this->createNotFoundException('Not found');
        }

        try {
            $groupManager->deleteGroup($group);
            $this->get('session')->getFlashBag()->add('warning', 'Deleted');
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('user_group_list'));
    }
}
