<?php

namespace Natue\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\UserBundle\Form\Type\User as UserForm;

/**
 * User controller
 *
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * List action
     *
     * @Route("/list", name="user_user_list")
     * @Template()
     * @Secure(roles="ROLE_SUPER_ADMIN,ROLE_USER_USER_READ")
     *
     * @return array
     */
    public function listAction()
    {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository   = $this->getDoctrine()->getRepository('NatueUserBundle:User');
        $queryBuilder = $repository->createQueryBuilder('user');

        /** @var \Natue\Bundle\UserBundle\Grid\UserGrid $grid */
        $grid = $this->get('pedroteixeira.grid')->createGrid('\Natue\Bundle\UserBundle\Grid\UserGrid');
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
     * @Route("/create", name="user_user_create")
     * @Template()
     * @Secure(roles="ROLE_SUPER_ADMIN,ROLE_USER_USER_CREATE")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');

        /* @var \Natue\Bundle\UserBundle\Entity\User $user */
        $user = $userManager->createUser(null);
        $form = $this->createForm(new UserForm(), $user);

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                if ($userManager->findUserByUsername($user->getUsername())) {
                    throw new \Exception('Username already exist');
                }

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                $userManager->updateUser($user);
                $this->get('session')->getFlashBag()->add('success', 'Created');

                if ($this->get('security.context')->isGranted(['ROLE_SUPER_ADMIN', 'ROLE_USER_USER_UPDATE'])) {
                    $this->get('session')->getFlashBag()->add(
                        'info',
                        $this->get('translator')->trans(
                            'Do you want to edit this row? <a href="%url%">Click here</a>',
                            ['%url%' => $this->generateUrl('user_user_update', ['id' => $user->getId()])]
                        )
                    );
                }

                return $this->redirect($this->generateUrl('user_user_create'));
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
     * @Route("/{id}/update", name="user_user_update")
     * @Template()
     * @Secure(roles="ROLE_SUPER_ADMIN,ROLE_USER_USER_UPDATE")
     *
     * @param                                           $id
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function updateAction($id, Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');

        /* @var User $user */
        $user = $userManager->findUserBy(['id' => $id]);

        if (!$user) {
            throw $this->createNotFoundException('Not found');
        }

        $form = $this->createForm(new UserForm(true), $user);

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                /* @var User $userDuplicated */
                $userDuplicated = $userManager->findUserByUsername($user->getUsername());

                if ($userDuplicated) {
                    if ($userDuplicated->getId() != $user->getId()) {
                        throw new \Exception('Username already exist');
                    }
                }

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                $userManager->updateUser($user);
                $this->get('session')->getFlashBag()->add('success', 'Updated');

                return $this->redirect($this->generateUrl('user_user_update', ['id' => $user->getId()]));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'user' => $user,
            'form' => $form->createView(),
        ];
    }

    /**
     * Delete action
     *
     * @Route("/{id}/delete", name="user_user_delete")
     * @Template()
     * @Secure(roles="ROLE_SUPER_ADMIN,ROLE_USER_USER_DELETE")
     *
     * @param int $id entity id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function deleteAction($id)
    {
        $userManager = $this->get('fos_user.user_manager');

        /* @var User $user */
        $user = $userManager->findUserBy(['id' => $id]);

        if (!$user) {
            throw $this->createNotFoundException('Not found');
        }

        try {
            $userManager->deleteUser($user);
            $this->get('session')->getFlashBag()->add('warning', 'Deleted');
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('user_user_list'));
    }
}
