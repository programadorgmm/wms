<?php

namespace Natue\Bundle\StockBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Natue\Bundle\StockBundle\Entity\StockPosition;
use Natue\Bundle\StockBundle\Form\Type\Position as PositionForm;

/**
 * Position controller
 *
 * @Route("/position")
 */
class PositionController extends Controller
{
    /**
     * List action
     *
     * @Route("/list", name="stock_position_list")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_POSITION_READ")
     *
     * @return array
     */
    public function listAction()
    {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository   = $this->getDoctrine()->getRepository('NatueStockBundle:StockPosition');
        $queryBuilder = $repository->createQueryBuilder('stockPosition');

        /** @var \Natue\Bundle\StockBundle\Grid\PositionGrid $grid */
        $grid = $this->get('pedroteixeira.grid')->createGrid('\Natue\Bundle\StockBundle\Grid\PositionGrid');
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
     * @Route("/create", name="stock_position_create")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_POSITION_CREATE")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $stockPosition = new StockPosition();
        $form          = $this->createForm(new PositionForm(), $stockPosition);

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                $entityManager->getConnection()->beginTransaction();

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                $stockPositionDuplicated = $this->getDoctrine()
                    ->getRepository('NatueStockBundle:StockPosition')
                    ->findOneBy(['name' => $stockPosition->getName()]);

                if ($stockPositionDuplicated) {
                    throw new \Exception('Position already exist');
                }

                $stockPosition->setUser($this->getUser());

                $entityManager->persist($stockPosition);
                $entityManager->flush();

                $this->get('session')->getFlashBag()->add('success', 'Created');

                if ($this->get('security.context')->isGranted(['ROLE_ADMIN', 'ROLE_STOCK_POSITION_UPDATE'])) {
                    $this->get('session')->getFlashBag()->add(
                        'info',
                        $this->get('translator')->trans(
                            'Do you want to edit this row? <a href="%url%">Click here</a>',
                            ['%url%' => $this->generateUrl('stock_position_update', ['id' => $stockPosition->getId()])]
                        )
                    );
                }

                $entityManager->getConnection()->commit();

                return $this->redirect($this->generateUrl('stock_position_create'));
            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();
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
     * @Route("/{id}/update", name="stock_position_update")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_POSITION_UPDATE")
     *
     * @param                                           $id
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function updateAction($id, Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        /* @var StockPosition $stockPosition */
        $stockPosition = $entityManager->getRepository('NatueStockBundle:StockPosition')
            ->findOneById($id);

        if (!$stockPosition) {
            throw $this->createNotFoundException('Not found');
        }

        $form = $this->createForm(new PositionForm(), $stockPosition);

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                $entityManager->getConnection()->beginTransaction();

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                /* @var StockPosition $stockPositionDuplicated */
                $stockPositionDuplicated = $this->getDoctrine()
                    ->getRepository('NatueStockBundle:StockPosition')
                    ->findOneBy(['name' => $stockPosition->getName()]);

                if ($stockPositionDuplicated && $stockPositionDuplicated->getId() != $stockPosition->getId()) {
                    throw new \Exception('Position already exist');
                }

                $entityManager->persist($stockPosition);
                $entityManager->flush();

                $this->get('session')->getFlashBag()->add('success', 'Updated');

                $entityManager->getConnection()->commit();

                return $this->redirect($this->generateUrl('stock_position_update', ['id' => $stockPosition->getId()]));
            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'stockPosition' => $stockPosition,
            'form'          => $form->createView(),
        ];
    }

    /**
     * Delete action
     *
     * @Route("/{id}/delete", name="stock_position_delete")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_POSITION_DELETE")
     *
     * @param int $id entity id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function deleteAction($id)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        /* @var StockPosition $stockPosition */
        $stockPosition = $entityManager->getRepository('NatueStockBundle:StockPosition')
            ->findOneById($id);

        if (!$stockPosition) {
            throw $this->createNotFoundException('Not found');
        }

        try {
            $entityManager->getConnection()->beginTransaction();
            $entityManager->remove($stockPosition);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('warning', 'Deleted');

            $entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('stock_position_list'));
    }
}
