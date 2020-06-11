<?php

namespace Natue\Bundle\StockBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Natue\Bundle\StockBundle\Form\Model\OrderRequest as OrderRequestModel;
use Natue\Bundle\StockBundle\Entity\OrderRequest;
use Natue\Bundle\StockBundle\Form\Type\OrderRequestType;

/**
 * OrderRequest controller.
 *
 * @Route("/order-request")
 */
class OrderRequestController extends Controller
{
    /**
     * Lists all OrderRequest entities.
     *
     * @Route("/", name="order-request")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('NatueStockBundle:OrderRequest')->findAll();

        return [
            'entities' => $entities,
        ];
    }

    /**
     * Creates a new OrderRequest entity.
     *
     * @Route("/", name="order-request_create")
     * @Method("POST")
     * @Template("NatueStockBundle:OrderRequest:new.html.twig")
     *
     * @var Request $request
     */
    public function createAction(Request $request)
    {
        $entityManager = $this->get('doctrine')->getManager();
        $model = new OrderRequestModel();
        $form = $this->createCreateForm($model);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $entityManager->getConnection()->beginTransaction();
                $manager = $this->get('natue.stock.orderrequest.manager');
                $entity = $manager->save($model);

                $this->get('session')->getFlashBag()->add('success', 'CSV Imported');
                $entityManager->getConnection()->commit();

                return $this->redirect($this->generateUrl('order-request_show', array('id' => $entity->getId())));
            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();

                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'entity' => $model,
            'form' => $form->createView(),
        ];
    }

    /**
     * Creates a form to create a OrderRequest entity.
     *
     * @param OrderRequestModel $model The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(OrderRequestModel $model)
    {
        $form = $this->createForm(new OrderRequestType(), $model, array(
            'action' => $this->generateUrl('order-request_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OrderRequest entity.
     *
     * @Route("/new", name="order-request_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $model = new OrderRequestModel();
        $form = $this->createCreateForm($model);

        return [
            'entity' => $model,
            'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a OrderRequest entity.
     *
     * @Route("/{id}", name="order-request_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('NatueStockBundle:OrderRequest')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OrderRequest entity.');
        }

        return [
            'entity' => $entity
        ];
    }
}
