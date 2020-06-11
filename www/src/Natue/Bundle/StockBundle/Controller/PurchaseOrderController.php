<?php

namespace Natue\Bundle\StockBundle\Controller;

use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Natue\Bundle\StockBundle\Grid\Datatable\PurchaseOrderGridBuilder;
use Natue\Bundle\CoreBundle\Validator\Constraints\ContainsExpirationWarning;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItem;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItemReception;
use Natue\Bundle\StockBundle\Service\PurchaseOrderReception;
use Natue\Bundle\StockBundle\Repository\PurchaseOrderRepository;
use Natue\Bundle\StockBundle\Form\Model\PurchaseOrderReceive;
use Natue\Bundle\StockBundle\Form\Model\PurchaseOrderItemDistribution;
use Natue\Bundle\StockBundle\Form\Model\PurchaseOrderVolumeConfirmation;
use Natue\Bundle\StockBundle\Form\Type\PurchaseOrder as PurchaseOrderForm;
use Natue\Bundle\StockBundle\Form\Type\PurchaseOrderReceive as PurchaseOrderReceiveForm;
use Natue\Bundle\StockBundle\Form\Type\PurchaseOrderItemDistribution as PurchaseOrderItemDistributionForm;
use Natue\Bundle\StockBundle\Form\Type\PurchaseOrderVolumeConfirmation as PurchaseOrderVolumeConfirmationForm;

/**
 * Purchase Order controller
 *
 * @Route("/purchase-order")
 */
class PurchaseOrderController extends Controller
{

    /**
     * List action
     *
     * @Route("/list", name="stock_purchase_order_list")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_READ")
     *
     * @return array
     */
    public function listAction()
    {
        return [
            'datatable' => $this->createPurchaseOrderGrid()
        ];
    }

    /**
     * Create action
     *
     * @Route("/create", name="stock_purchase_order_create")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_CREATE")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $purchaseOrder = new PurchaseOrder();
        $form          = $this->createForm(new PurchaseOrderForm(), $purchaseOrder);

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                $entityManager->getConnection()->beginTransaction();

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                /* @var PurchaseOrder $purchaseOrderDuplicated */
                $purchaseOrderDuplicated = $this->getDoctrine()->getRepository('NatueStockBundle:PurchaseOrder')
                    ->findOneBy(['invoiceKey' => $purchaseOrder->getInvoiceKey()]);

                if ($purchaseOrderDuplicated) {
                    throw new \Exception('Invoice key already exists');
                }

                $purchaseOrder->setUser($this->getUser());

                $entityManager->persist($purchaseOrder);
                $entityManager->flush();

                $this->get('session')->getFlashBag()->add('success', 'Created');
                $this->get('session')->getFlashBag()->add(
                    'info',
                    $this->get('translator')->trans(
                        'Do you want to download the PDF? <a href="%url%">Click here</a>',
                        [
                            '%url%' => $this->generateUrl(
                                'pdf_generate_purchase_order_volume',
                                [
                                    'id'           => $purchaseOrder->getId(),
                                    'volumesTotal' => $purchaseOrder->getVolumesTotal()
                                ]
                            )
                        ]
                    )
                );

                if ($this->get('security.context')->isGranted(['ROLE_ADMIN', 'ROLE_STOCK_PURCHASE_ORDER_UPDATE'])) {
                    $this->get('session')->getFlashBag()->add(
                        'info',
                        $this->get('translator')->trans(
                            'Do you want to edit this row? <a href="%url%">Click here</a>',
                            [
                                '%url%' => $this->generateUrl(
                                    'stock_purchase_order_update',
                                    ['id' => $purchaseOrder->getId()]
                                )
                            ]
                        )
                    );
                }

                $entityManager->getConnection()->commit();

                return $this->redirect($this->generateUrl('stock_purchase_order_create'));
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
     * @Route("/{id}/update", name="stock_purchase_order_update", options={"expose"=true})
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_UPDATE")
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

        /* @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $entityManager->getRepository('NatueStockBundle:PurchaseOrder')
            ->findOneById($id);

        if (!$purchaseOrder) {
            throw $this->createNotFoundException('Not found');
        }

        $form = $this->createForm(new PurchaseOrderForm(), $purchaseOrder);

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                $entityManager->getConnection()->beginTransaction();

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                /* @var PurchaseOrder $purchaseOrderDuplicated */
                $purchaseOrderDuplicated = $this->getDoctrine()->getRepository('NatueStockBundle:PurchaseOrder')
                    ->findOneBy(['invoiceKey' => $purchaseOrder->getInvoiceKey()]);

                if ($purchaseOrderDuplicated && $purchaseOrderDuplicated->getId() != $purchaseOrder->getId()) {
                    throw new \Exception('Invoice key already exists');
                }

                $entityManager->persist($purchaseOrder);
                $entityManager->flush();

                $this->get('session')->getFlashBag()->add('success', 'Updated');
                $entityManager->getConnection()->commit();

                return $this->redirect(
                    $this->generateUrl('stock_purchase_order_update', ['id' => $purchaseOrder->getId()])
                );
            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'purchaseOrder' => $purchaseOrder,
            'form'          => $form->createView(),
        ];
    }

    /**
     * Delete action
     *
     * @Route("/{id}/delete", name="stock_purchase_order_delete", options={"expose"=true})
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_DELETE")
     *
     * @param int $id entity id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     * @return array
     */
    public function deleteAction($id)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        /* @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $entityManager->getRepository('NatueStockBundle:PurchaseOrder')
            ->findOneById($id);

        if (!$purchaseOrder) {
            throw $this->createNotFoundException('Not found');
        }

        /* @var PurchaseOrderItem $purchaseOrderItem */
        $purchaseOrderItem = $entityManager->getRepository('NatueStockBundle:PurchaseOrderItem')
            ->findOneByPurchaseOrder($id);

        if ($purchaseOrderItem) {
            throw new \Exception('This purchase order contains purchase order items, delete is impossible.');
        }

        try {
            $entityManager->getConnection()->beginTransaction();
            $entityManager->remove($purchaseOrder);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('warning', 'Deleted');
            $entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('stock_purchase_order_list'));
    }

    /**
     * Receiving volumes of Purchase Order
     *
     * @Route("/receive-volumes", name="stock_purchase_order_receive_volumes")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_RECEIVE_VOLUMES")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function receiveVolumesAction(Request $request)
    {
        /** @var PurchaseOrderRepository $purchaseOrderRepository */
        $purchaseOrderRepository = $this->getDoctrine()->getRepository('NatueStockBundle:PurchaseOrder');

        // Guard: check, if current user don't have any receiving work in progress
        $inProgressPurchaseOrderId = $purchaseOrderRepository
            ->getUserInProgressPurchaseOrderId($this->getUser()->getId());
        if ($inProgressPurchaseOrderId) {
            $this->get('session')->getFlashBag()->add('danger', 'Please finish open Purchase Order reception');
            return $this->redirect(
                $this->generateUrl('stock_purchase_order_items_distribution', ['id' => $inProgressPurchaseOrderId])
            );
        }

        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $purchaseOrderReceive = new PurchaseOrderReceive();
        $form = $this->createForm(new PurchaseOrderReceiveForm(), $purchaseOrderReceive);

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                $entityManager->getConnection()->beginTransaction();

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission: ' . $form->getErrors(true));
                }

                $purchaseOrder = $purchaseOrderRepository->findOneByInvoiceKeyOrPurchaseOrderId(
                    $purchaseOrderReceive->getPurchaseOrderReference()
                );

                if (!$purchaseOrder) {
                    throw $this->createNotFoundException('Purchase Order not found');
                }

                $volumesLeft = $purchaseOrder->getVolumesTotal() - $purchaseOrder->getVolumesReceived();

                if ($volumesLeft <= 0) {
                    throw new \Exception('Purchase Order already received');
                }

                if ($purchaseOrderReceive->getVolumes() > $volumesLeft) {
                    throw new \Exception('Requested volumes amount is higher than volumes left');
                }


                // Create a new Reception
                $purchaseOrderItemReception = new PurchaseOrderItemReception();
                $purchaseOrderItemReception->setPurchaseOrder($purchaseOrder);
                $purchaseOrderItemReception->setUser($this->getUser());
                $purchaseOrderItemReception->setVolumes($purchaseOrderReceive->getVolumes());
                $entityManager->persist($purchaseOrderItemReception);
                $entityManager->flush();


                $this->get('session')->getFlashBag()->add('success', 'PurchaseOrder Reception has been created');
                $entityManager->getConnection()->commit();

                return $this->redirect(
                    $this->generateUrl(
                        'stock_purchase_order_items_distribution',
                        [
                            'id' => $purchaseOrder->getId()
                        ]
                    )
                );

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
     * Items distribution action
     *
     * @Route("/{id}/items-distribution", name="stock_purchase_order_items_distribution")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_RECEIVE_VOLUMES")
     *
     * @param         $id
     * @param Request $request
     *
     * @return array|\PedroTeixeira\Bundle\GridBundle\Grid\GridView|\Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     */
    public function itemsDistributionAction($id, Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->getDoctrine()->getRepository('NatueStockBundle:PurchaseOrder')->findOneById($id);

        if (!$purchaseOrder) {
            throw $this->createNotFoundException('Purchase Order not found');
        }

        if ($purchaseOrder->getVolumesReceived() >= $purchaseOrder->getVolumesTotal()) {
            throw new \Exception('Purchase Order already received');
        }

        $purchaseOrderItemDistribution = new PurchaseOrderItemDistribution();
        $form                          = $this->createForm(
            new PurchaseOrderItemDistributionForm(),
            $purchaseOrderItemDistribution
        );

        /** @var PurchaseOrderReception $purchaseOrderReceptionService */
        $purchaseOrderReceptionService = $this->get('natue.stock.purchaseorder.reception');

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                $entityManager->getConnection()->beginTransaction();

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission: ' . $form->getErrors(true));
                }

                $purchaseOrderReceptionService->putPurchaseOrderItemsAtPosition(
                    $purchaseOrder,
                    $purchaseOrderItemDistribution->getBarcode(),
                    $purchaseOrderItemDistribution->getDateExpiration(),
                    $purchaseOrderItemDistribution->getQuantity(),
                    $purchaseOrderItemDistribution->getPosition()
                );

                $this->get('session')->getFlashBag()->add('success', 'Item has been assigned to position');

                if (ContainsExpirationWarning::check($purchaseOrderItemDistribution->getDateExpiration())) {
                    $this->get('session')->getFlashBag()->add('warning', ContainsExpirationWarning::getMessage());
                }

                $entityManager->getConnection()->commit();
            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        $gridData = $purchaseOrderReceptionService->getDataForDistributionTable($purchaseOrder);

        $purchaseOrderVolumeConfirmation = new PurchaseOrderVolumeConfirmation();
        $purchaseOrderVolumeConfirmation->setPurchaseOrderId($id);
        $confirmationForm                = $this->createForm(
            new PurchaseOrderVolumeConfirmationForm(),
            $purchaseOrderVolumeConfirmation
        );

        return [
            'gridData'         => $gridData,
            'purchaseOrder'    => $purchaseOrder,
            'form'             => $form->createView(),
            'confirmationForm' => $confirmationForm->createView(),
        ];
    }

    /**
     * Confirm current Volume distribution for posted Purchase Order
     *  + increase number of volume received within "purchase_order" table
     *
     * @Route("/confirm-volume-distribution", name="stock_purchase_order_confirm_volume_distribution")
     * @Method({"POST"})
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_RECEIVE_VOLUMES")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function confirmVolumeDistributionAction(Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $purchaseOrderVolumeConfirmation = new PurchaseOrderVolumeConfirmation();
        $confirmationForm                = $this->createForm(
            new PurchaseOrderVolumeConfirmationForm(),
            $purchaseOrderVolumeConfirmation
        );

        $confirmationForm->submit($request);

        $purchaseOrderId = $purchaseOrderVolumeConfirmation->getPurchaseOrderId();

        try {
            $entityManager->getConnection()->beginTransaction();

            if (!$confirmationForm->isValid()) {
                throw new \Exception('Error on form submission');
            }

            /** @var PurchaseOrderReception $purchaseOrderReceptionService */
            $purchaseOrderReceptionService = $this->get('natue.stock.purchaseorder.reception');
            $purchaseOrderReceptionService->confirmVolumeDistribution($purchaseOrderId);

            $this->get('session')->getFlashBag()->add('success', 'Volume distribution confirmed');
            $entityManager->getConnection()->commit();

            return $this->redirect($this->generateUrl('stock_purchase_order_list'));

        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();

            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());

            return $this->redirect(
                $this->generateUrl(
                    'stock_purchase_order_items_distribution',
                    [
                        'id' => $purchaseOrderId
                    ]
                )
            );
        }
    }

    /**
     * Cancel Volume distribution
     *
     * @Route("/cancel-volume-distribution/{purchaseOrderId}", name="stock_purchase_order_cancel_volume_distribution")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_RECEIVE_VOLUMES")
     *
     * @param int $purchaseOrderId
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cancelVolumeDistributionAction($purchaseOrderId)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        try {
            $entityManager->getConnection()->beginTransaction();

            /** @var PurchaseOrderReception $purchaseOrderReceptionService */
            $purchaseOrderReceptionService = $this->get('natue.stock.purchaseorder.reception');
            $purchaseOrderReceptionService->tryCancelVolumeDistribution($purchaseOrderId);

            $this->get('session')->getFlashBag()->add('success', 'Volume distribution canceled');
            $entityManager->getConnection()->commit();

            return $this->redirect($this->generateUrl('stock_purchase_order_list'));

        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();

            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());

            return $this->redirect(
                $this->generateUrl(
                    'stock_purchase_order_items_distribution',
                    [
                        'id' => $purchaseOrderId
                    ]
                )
            );
        }
    }

    /**
     * @Route("/results", name="purchase_order_results", options={"expose"=true})
     */
    public function indexResultsAction(Request $request)
    {
        $datatable = $this->createPurchaseOrderGrid();

        /** @var \Natue\Bundle\CoreBundle\Datatable\Query\DatatableQuery $query */
        $query = $this->get('natue_datatables.query')->getQueryFrom($datatable);

        $query->addWhereAll(function ($queryBuilder) {
            $queryBuilder
                ->addGroupBy('purchase_order.id');
        });

        return $query->getResponse();
    }

    /**
     * @return \Natue\Bundle\StockBundle\Grid\Datatable\PurchaseOrderGridBuilder
     */
    protected function createPurchaseOrderGrid()
    {
        /** @var PurchaseOrderGridBuilder $datatableBuilder */
        $datatableBuilder = $this->get('natue.stock.purchaseorder.grid.builder');

        return $datatableBuilder
            ->setAjaxSettings(
                [
                    'url'  => $this->generateUrl('purchase_order_results'),
                    'type' => 'GET'
                ]
            )
            ->addIdColumn()
            ->addInvoiceKeyColumn()
            ->addCreatedAtColumn()
            ->addDeliveredAtColumn()
            ->addSupplierColumn()
            ->addActionsColumn()
            ->build();
    }
}
