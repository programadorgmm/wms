<?php

namespace Natue\Bundle\StockBundle\Controller;

use Natue\Bundle\StockBundle\Form\Model\XmlNotMatched;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItem;
use Natue\Bundle\StockBundle\Form\Type\PurchaseOrderItem as PurchaseOrderItemFormType;
use Natue\Bundle\StockBundle\Form\Type\PurchaseOrderItemXml as PurchaseOrderItemXmlForm;
use Natue\Bundle\StockBundle\Form\Type\PurchaseOrderItemCsv as PurchaseOrderItemCsvForm;
use Natue\Bundle\StockBundle\Form\Type\PurchaseOrderItemCostAverageUpdate;
use Natue\Bundle\StockBundle\Form\Model\PurchaseOrderItem as PurchaseOrderItemFormModel;
use Natue\Bundle\StockBundle\Form\Model\PurchaseOrderItemCostAverageUpdate as PurchaseOrderItemCostAverageUpdateModel;
use Natue\Bundle\StockBundle\Form\Model\PurchaseOrderItemXml as PurchaseOrderItemXmlFormModel;
use Natue\Bundle\StockBundle\Form\Model\PurchaseOrderItemCsv as PurchaseOrderItemCsvFormModel;
use Natue\Bundle\StockBundle\Repository\PurchaseOrderItemRepository;
use Natue\Bundle\StockBundle\StateMachine\StockItem as StockItemStateMachine;
use Natue\Bundle\StockBundle\Service\PurchaseOrderReception;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;
use Natue\Bundle\StockBundle\Form\Model\XmlNotMatched as XmlNotMatchedModel;
use Natue\Bundle\StockBundle\Form\Type\XmlNotMatched as XmlNotMatchedType;
use Natue\Bundle\StockBundle\Entity\OrderRequest;
use Predis\Client as RedisClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * Purchase Order Item controller
 *
 * @Route("/purchase-order/item")
 */
class PurchaseOrderItemController extends Controller
{
    /**
     * List action
     *
     * @Route("/{id}/list", name="stock_purchase_order_item_list", options={"expose"=true})
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_READ")
     *
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function listAction($id)
    {
        /** @var RedisClient $redisClient **/
        $redisClient = $this->get('snc_redis.default');

        $orderMeta = unserialize($redisClient->get('purchase_order:'.$id));

        if ($orderMeta) {
            return $this->redirect($this->generateUrl('stock_purchase_order_item_xml_check', ['id' => $id]));
        }


        /** @var PurchaseOrderReception $purchaseOrderReceptionService */
        $purchaseOrderReceptionService = $this->get('natue.stock.purchaseorder.reception');

        /** @var \Natue\Bundle\StockBundle\Entity\PurchaseOrder $purchaseOrder */
        $purchaseOrder = $purchaseOrderReceptionService->getPurchaseOrderDetails($id);

        if (!$purchaseOrder) {
            throw $this->createNotFoundException('Purchase Order not found');
        }

        $queryBuilder = $purchaseOrderReceptionService->getQueryBuilderForOrderItemsGrid($purchaseOrder);
        $path = $this->generateUrl('stock_purchase_order_item_list', ['id' => $id]);

        /** @var \Natue\Bundle\StockBundle\Grid\PurchaseOrderItemGrid $grid */
        $grid = $this->get('pedroteixeira.grid')->createGrid('\Natue\Bundle\StockBundle\Grid\PurchaseOrderItemGrid');
        $grid->setQueryBuilder($queryBuilder);
        $grid->setUrl($path);

        if ($grid->isResponseAnswer()) {
            return $grid->render();
        }

        $formView = null;
        $formCsvView = null;

        $gridData = $grid->getData();

        if ((isset($gridData['row_count']) && $gridData['row_count'] == 0)
            && ($this->get('security.context')->isGranted(['ROLE_ADMIN', 'ROLE_STOCK_PURCHASE_ORDER_ITEM_CSV']))
        ) {
            $form = $this->createForm(new PurchaseOrderItemXmlForm(), new PurchaseOrderItemXmlFormModel());
            $formView = $form->createView();

            $formCsv = $this->createForm(new PurchaseOrderItemCsvForm(), new PurchaseOrderItemCsvFormModel());
            $formCsvView = $formCsv->createView();
        }

        return [
            'grid' => $grid->render(),
            'form' => $formView,
            'formCsv' => $formCsvView,
            'purchaseOrder' => $purchaseOrder,
        ];
    }

    /**
     * Create action
     *
     * @Route("/{id}/create", name="stock_purchase_order_item_create")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_CREATE")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param integer $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return array
     */
    public function createAction(Request $request, $id)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $entityManager->getRepository('NatueStockBundle:PurchaseOrder')
            ->findOneById($id);

        if (!$purchaseOrder) {
            throw $this->createNotFoundException('Purchase Order not found');
        }

        $purchaseOrderItemFormModel = new PurchaseOrderItemFormModel();
        $form = $this->createForm(
            new PurchaseOrderItemFormType(),
            $purchaseOrderItemFormModel
        );

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                $entityManager->getConnection()->beginTransaction();
                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                $quantity = $purchaseOrderItemFormModel->getQuantity();
                $zedProduct = $purchaseOrderItemFormModel->getZedProduct();
                $cost = $purchaseOrderItemFormModel->getCost();
                $purchaseOrderItem = new PurchaseOrderItem();
                $purchaseOrderItem->setZedProduct($zedProduct);
                $purchaseOrderItem->setCost($cost);
                $purchaseOrderItem->setInvoiceCost($purchaseOrderItemFormModel->getInvoiceCost());
                $purchaseOrderItem->setIcms($purchaseOrderItemFormModel->getIcms());
                $purchaseOrderItem->setIcmsSt($purchaseOrderItemFormModel->getIcmsSt());
                $purchaseOrderItem->setPurchaseOrder($purchaseOrder);

                /** @var \Natue\Bundle\StockBundle\Service\BatchProcessingPurchaseOrderItem $batchProcessing */
                $batchProcessing = $this->get('natue.stock.purchaseorder.item.batchprocessing');
                $batchProcessing->bulkInsert(
                    $quantity,
                    $purchaseOrderItem
                );

                $this->get('session')->getFlashBag()->add('success', 'Created');

                if ($this->get('security.context')->isGranted(
                    ['ROLE_ADMIN', 'ROLE_STOCK_PURCHASE_ORDER_ITEM_UPDATE']
                )
                ) {
                    $this->get('session')->getFlashBag()->add(
                        'info',
                        $this->get('translator')->trans(
                            'Do you want to edit this row? <a href="%url%">Click here</a>',
                            [
                                '%url%' => $this->generateUrl(
                                    'stock_purchase_order_item_update',
                                    [
                                        'purchaseOrderId' => $purchaseOrderItem->getPurchaseOrder()->getId(),
                                        'cost' => $purchaseOrderItem->getCost(),
                                        'sku' => $purchaseOrderItem->getZedProduct()->getSku()
                                    ]
                                )
                            ]
                        )
                    );
                }

                $entityManager->getConnection()->commit();

                return $this->redirect($this->generateUrl('stock_purchase_order_item_create', ['id' => $id]));

            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'id' => $id,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{purchaseOrderId}/cost-average/{sku}/{cost}/update", name="stock_purchase_order_cost_average_update")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_UPDATE")
     * @param integer $purchaseOrderId
     * @param integer $sku
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     * @return array
     */
    public function updateCostAverageAction($purchaseOrderId, $cost, $sku, Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $entityManager->getRepository('NatueStockBundle:PurchaseOrder')
            ->findOneById($purchaseOrderId);

        if (!$purchaseOrder) {
            throw $this->createNotFoundException('Purchase Order with id:' . $purchaseOrderId . ' not found');
        }

        /** @var ZedProduct $zedProduct */
        $zedProduct = $entityManager->getRepository('NatueZedBundle:ZedProduct')
            ->findOneBySku($sku);

        if (!$zedProduct) {
            throw $this->createNotFoundException('Zed Product with sku:' . $sku . ' not found');
        }

        $purchaseOrderItemRepository = $this->getDoctrine()->getRepository('NatueStockBundle:PurchaseOrderItem');
        $purchaseOrderItems = $purchaseOrderItemRepository->findByZedProductAndCostAndPurchaseOrder(
            $zedProduct,
            $cost,
            $purchaseOrder
        );

        if (empty($purchaseOrderItems)) {
            throw new \Exception('Purchase Order Item not found');
        }

        $purchaseOrderItemFormModel = new PurchaseOrderItemCostAverageUpdateModel();
        $purchaseOrderItemFormModel->setCost($purchaseOrderItems[0]->getCost());
        $purchaseOrderItemFormModel->setQuantity(count($purchaseOrderItems));
        $purchaseOrderItemFormModel->setZedProduct($purchaseOrderItems[0]->getZedProduct());

        $form = $this->createForm(
            new PurchaseOrderItemCostAverageUpdate(),
            $purchaseOrderItemFormModel
        );

        if ($request->isMethod('post')) {
            $form->submit($request);

            if (!$form->isValid()) {
                throw new \Exception('Error on form submission');
            }

            try {
                $entityManager->getConnection()->beginTransaction();

                $purchaseOrderReceptionService = $this->get('natue.stock.purchaseorder.reception');
                $purchaseOrderReceptionService
                    ->updatePurchaseOrderCostAverage(
                        $purchaseOrderItems,
                        $form->getData()->getCost(),
                        $purchaseOrder,
                        $zedProduct
                    );

                $this->get('session')->getFlashBag()->add('success', 'All Costs were updated');

                $entityManager->getConnection()->commit();

                return $this->redirect(
                    $this->generateUrl('stock_purchase_order_item_list', ['id' => $purchaseOrder->getId()])
                );
            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'purchaseOrderId' => $purchaseOrderId,
            'cost' => $cost,
            'sku' => $sku,
            'form' => $form->createView(),
        ];
    }

    /**
     * Update action
     *
     * @Route("/{purchaseOrderId}/{cost}/{sku}/update", name="stock_purchase_order_item_update")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_UPDATE")
     *
     * @param integer $purchaseOrderId
     * @param integer $cost
     * @param integer $sku
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     * @return array
     */
    public function updateAction($purchaseOrderId, $cost, $sku, Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        /** @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $entityManager->getRepository('NatueStockBundle:PurchaseOrder')
            ->findOneById($purchaseOrderId);
        if (!$purchaseOrder) {
            throw $this->createNotFoundException('Purchase Order with id:' . $purchaseOrderId . ' not found');
        }

        /** @var ZedProduct $zedProduct */
        $zedProduct = $entityManager->getRepository('NatueZedBundle:ZedProduct')
            ->findOneBySku($sku);
        if (!$zedProduct) {
            throw $this->createNotFoundException('Zed Product with sku:' . $sku . ' not found');
        }

        $purchaseOrderItemRepository = $this->getDoctrine()->getRepository('NatueStockBundle:PurchaseOrderItem');
        $purchaseOrderItem = $purchaseOrderItemRepository->findOneByZedProductAndCostAndPurchaseOrder(
            $zedProduct,
            $cost,
            $purchaseOrder
        );

        if (!$purchaseOrderItem) {
            throw new \Exception('Purchase Order Item not found');
        }

        $purchaseOrderItemFormModel = new PurchaseOrderItemFormModel();

        $purchaseOrderItemFormModel->setCost($purchaseOrderItem->getCost());
        $purchaseOrderItemFormModel->setQuantity($purchaseOrderItemRepository->countByZedProductAndCostAndPurchaseOrder(
            $zedProduct, $cost, $purchaseOrder
        ));
        $purchaseOrderItemFormModel->setZedProduct($purchaseOrderItem->getZedProduct());
        $purchaseOrderItemFormModel->setIcmsSt($purchaseOrderItem->getIcmsSt());
        $purchaseOrderItemFormModel->setIcms($purchaseOrderItem->getIcms());
        $purchaseOrderItemFormModel->setInvoiceCost($purchaseOrderItem->getInvoiceCost());

        $form = $this->createForm(
            new PurchaseOrderItemFormType(),
            $purchaseOrderItemFormModel,
            ['isZedProductDisabled' => true]
        );

        $initialStatus = StockItemStateMachine::INITIAL_STATUS;

        try {
            $entityManager->getConnection()->beginTransaction();
            $purchaseOrderItemListWithInitialStatus = $purchaseOrderItemRepository
                ->findByZedProductAndCostAndPurchaseOrderAndStatus(
                    $zedProduct,
                    $cost,
                    $purchaseOrder,
                    $initialStatus,
                    true
                );
            if (!$purchaseOrderItemListWithInitialStatus) {
                throw new \Exception('Purchase Order Item not found');
            }

            // We have to recreate the form to show the number of items (quantity).
            $availableQuantityItems = count($purchaseOrderItemListWithInitialStatus);
            $purchaseOrderItemFormModel->setQuantity($availableQuantityItems);

            if ($request->isMethod('post')) {
                $form->submit($request);

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                $newCost = $purchaseOrderItemFormModel->getCost();
                $newQuantity = $purchaseOrderItemFormModel->getQuantity();
                $icmsSt = $purchaseOrderItemFormModel->getIcmsSt();
                $icms = $purchaseOrderItemFormModel->getIcms();
                $invoiceCost = $purchaseOrderItemFormModel->getInvoiceCost();

                $quantityDiff = $newQuantity - $availableQuantityItems;

                /** @var \Natue\Bundle\StockBundle\Service\BatchProcessingPurchaseOrderItem $batchProcessing */
                $batchProcessing = $this->get('natue.stock.purchaseorder.item.batchprocessing');

                if ($quantityDiff != 0) {
                    if ($quantityDiff < 0) {
                        $batchProcessing->bulkDelete(
                            $purchaseOrder,
                            $cost,
                            $zedProduct,
                            $initialStatus,
                            abs($quantityDiff)
                        );
                    } elseif ($quantityDiff > 0) {
                        $batchProcessing->bulkInsert(
                            $quantityDiff,
                            $purchaseOrderItem,
                            $initialStatus
                        );
                    }
                }

                $batchProcessing->bulkUpdate(
                    $purchaseOrder,
                    $cost,
                    $zedProduct,
                    $newCost,
                    $icmsSt,
                    $icms,
                    $invoiceCost,
                    $initialStatus
                );

                $this->get('session')->getFlashBag()->add('success', 'Updated');
                $entityManager->getConnection()->commit();

                return $this->redirect(
                    $this->generateUrl('stock_purchase_order_item_list', ['id' => $purchaseOrder->getId()])
                );
            }

            $entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return [
            'purchaseOrderId' => $purchaseOrderId,
            'cost' => $cost,
            'sku' => $sku,
            'form' => $form->createView(),
        ];
    }

    /**
     * Delete action
     *
     * @Route(
     *   "/{purchaseOrderId}/{cost}/{sku}/{status}/delete",
     *   name="stock_purchase_order_item_delete"
     * )
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_DELETE")
     *
     * @param integer $purchaseOrderId
     * @param integer $cost
     * @param integer $sku
     * @param string $status
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     * @return array
     */
    public function deleteAction($purchaseOrderId, $cost, $sku, $status)
    {
        /* @var \Doctrine\ORM\EntityManager $em */
        $entityManager = $this->getDoctrine()->getManager();

        /* @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $entityManager->getRepository('NatueStockBundle:PurchaseOrder')
            ->findOneById($purchaseOrderId);
        if (!$purchaseOrder) {
            throw $this->createNotFoundException('Purchase Order with id:' . $purchaseOrderId . ' not found');
        }

        /* @var ZedProduct $zedProduct */
        $zedProduct = $entityManager->getRepository('NatueZedBundle:ZedProduct')
            ->findOneBySku($sku);
        if (!$zedProduct) {
            throw $this->createNotFoundException('Zed Product with sku:' . $sku . ' not found');
        }

        /** @var PurchaseOrderItemRepository $purchaseOrderItemRepository */
        $purchaseOrderItemRepository = $this->getDoctrine()->getRepository('NatueStockBundle:PurchaseOrderItem');

        $purchaseOrderItemCount = $purchaseOrderItemRepository
            ->countByZedProductAndCostAndPurchaseOrderAndStatus(
                $zedProduct,
                $cost,
                $purchaseOrder,
                $status
            );

        if ($purchaseOrderItemCount <= 0) {
            throw new \Exception('No Purchase Order Items were found');
        }

        try {
            $entityManager->getConnection()->beginTransaction();

            /** @var \Natue\Bundle\StockBundle\Service\BatchProcessingPurchaseOrderItem $batchProcessing */
            $batchProcessing = $this->get('natue.stock.purchaseorder.item.batchprocessing');
            $batchProcessing->bulkDelete(
                $purchaseOrder,
                $cost,
                $zedProduct,
                StockItemStateMachine::INITIAL_STATUS
            );

            $this->get('session')->getFlashBag()->add('warning', 'Deleted');
            $entityManager->getConnection()->commit();

        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl('stock_purchase_order_item_list', ['id' => $purchaseOrder->getId()])
        );
    }

    /**
     * Generate a form to upload csv files action
     *
     * @Route("/{id}/csv", name="stock_purchase_order_item_csv")
     * @Method({"POST"})
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_CSV")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int                                       $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\Form\FormFactory
     */
    public function csvAction(Request $request, $id)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        /* @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $entityManager->getRepository('NatueStockBundle:PurchaseOrder')
            ->findOneById($id);

        if (!$purchaseOrder) {
            throw $this->createNotFoundException('Purchase Order not found');
        }

        $form = $this->createForm(
            new PurchaseOrderItemCsvForm(),
            new PurchaseOrderItemCsvFormModel()
        );
        $form->submit($request);
        try {
            $entityManager->getConnection()->beginTransaction();
            if (!$form->isValid()) {
                throw new \Exception('Error on form submission: ' . $form->getErrors(true));
            }
            $file = $form->get('submitFile');
            $csvPathFolder = sys_get_temp_dir();
            $csvPathFile   = 'listItems_' . uniqid() . '.csv';
            $csvPath       = $csvPathFolder . DIRECTORY_SEPARATOR . $csvPathFile;
            $file->getData()->move($csvPathFolder, $csvPathFile);
            $formProcess = $this->get('natue.stock.purchaseorder.item.csv');
            $formProcess->processCsv($entityManager, $id, $csvPath);
            $entityManager->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('success', 'CSV imported');
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }
        return $this->redirect($this->generateUrl('stock_purchase_order_item_list', ['id' => $id]));
    }

    /**
     * Generate a form to upload csv files action
     *
     * @Route("/{id}/xml", name="stock_purchase_order_item_xml")
     * @Method({"POST"})
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_CSV")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\Form\FormFactory
     */
    public function xmlAction(Request $request, $id)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        /* @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $entityManager->getRepository('NatueStockBundle:PurchaseOrder')
            ->findOneById($id);

        if (!$purchaseOrder) {
            throw $this->createNotFoundException('Purchase Order not found');
        }

        $form = $this->createForm(
            new PurchaseOrderItemXmlForm(),
            new PurchaseOrderItemXmlFormModel()
        );

        $form->handleRequest($request);

        try {
            if (!$form->isValid()) {
                throw new \Exception('Error on form submission: ' . $form->getErrors(true));
            }

            $shippingCost = $form->get('shippingCost')->getData();

            $formProcess = $this->get('natue.stock.purchaseorder.item.xml');
            $orderMeta = $formProcess->processXml($form->get('submitFile')->getData()->getPathname(), $purchaseOrder->getZedSupplier()->getId(), $shippingCost);
            $orderMeta['order_request'] = $form->get('orderRequest')->getData()->getId();

            $this->get('session')->set('order_meta', $orderMeta);
            $this->get('session')->getFlashBag()->add('success', 'XML imported');

            return $this->redirect($this->generateUrl('stock_purchase_order_item_xml_check', ['id' => $id]));
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('stock_purchase_order_item_list', ['id' => $id]));
    }

    /**
     * Generate a form to \files action
     *
     * @Route("/{id}/xml/check", name="stock_purchase_order_item_xml_check")
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_CSV")
     * @Template()
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     *
     * @throws \Exception
     * @return array
     */
    public function checkAction(Request $request, $id)
    {
        $orderMeta = $this->get('session')->get('order_meta');
        $acceptedByCommercial = false;

        if (!$orderMeta) {
            /** @var RedisClient $redisClient **/
            $redisClient = $this->get('snc_redis.default');

            $orderMeta = unserialize($redisClient->get('purchase_order:'.$id));
            $acceptedByCommercial = !empty($orderMeta);
        }

        if (!$orderMeta) {
            return $this->redirect($this->generateUrl('stock_purchase_order_item_list', ['id' => $id]));
        }

        if ($orderMeta['not_matched']) {
            return $this->handleNotMatched($request, $id);
        }

        $entityManager = $this->get('doctrine')->getManager();
        $xmlManager = $this->get('natue.stock.purchaseorder.item.xml');
        $diffOrderRequest = $xmlManager->diffOrderRequest($orderMeta);
        $orderRequest = $entityManager->getRepository('NatueStockBundle:OrderRequest')->findOneById($orderMeta['order_request']);

        return [
            'zed_product_link' => $this->container->getParameter('zed_host_name') . 'catalog/product/edit/id/%s/',
            'order_request' => $orderRequest,
            'total_not_matched' => $orderMeta['not_matched'],
            'in_request' => $diffOrderRequest['in_request'],
            'not_in_request' => $diffOrderRequest['not_in_request'],
            'not_in_nfe' => $diffOrderRequest['not_in_nfe'],
            'total_items_at_order_request' => $entityManager->getRepository('NatueStockBundle:OrderRequestItem')->countItemsAtOrderRequest($orderRequest),
            'total_items_at_nfe' => $orderMeta['total_quantity'],
            'accepted_by_commercial' => $acceptedByCommercial,
            'id' => $id,
        ];
    }

    public function handleNotMatched(Request $request, $id)
    {
        $entityManager = $this->get('doctrine')->getManager();
        $xmlManager = $this->get('natue.stock.purchaseorder.item.xml');
        $orderMeta = $this->get('session')->get('order_meta');
        $orderRequest = $entityManager->getRepository('NatueStockBundle:OrderRequest')->findOneById($orderMeta['order_request']);

        $items = $xmlManager->getNotMatchedItems($orderMeta['products']);
        $xmlNotMatchedModel = new XmlNotMatchedModel();
        $xmlNotMatchedModel->setItems($items);

        $form = $this->get('form.factory')->create(new XmlNotMatchedType(), $xmlNotMatchedModel);

        if ($request->isMethod('post')) {
            $form->submit($request);

            if (!$form->isValid()) {
                throw new \Exception('Error on form submission: ' . $form->getErrors(true));
            }

            foreach ($form->getData()->getItems() as $item) {
                $productData = array_merge($xmlManager->getItemData($item), [
                    'quantity' => $item->getQuantity(),
                    'supplier' => $orderMeta['products'][$item->getXmlCode()]['supplier'],
                    'nfe_sequential' => $item->getNfeSequential()
                ]);

                $orderMeta['products'][$item->getXmlCode()] = $xmlManager->calculateProductInfo($productData);

                $orderMeta['total_quantity'] += $item->getQuantity();
                $orderMeta['not_matched']--;
            }


            $this->get('session')->set('order_meta', $orderMeta);

            return $this->redirect($this->generateUrl('stock_purchase_order_item_xml_check', ['id' => $id]));
        }

        return [
            'not_matched_form' => $form->createView(),
            'total_not_matched' => $orderMeta['not_matched'],
            'order_request' => $orderRequest,
        ];
    }

    /**
     * @Route("/{id}/xml/commercial/accept", name="stock_purchase_order_item_xml_commercial_accept")
     * @Method({"GET"})
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_CSV")
     * @Template()
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     *
     * @throws \Exception
     * @return array
     */
    public function commercialAcceptAction(Request $request, $id)
    {
        $orderMeta = $this->get('session')->get('order_meta');
        $expireAt = $this->container->getParameter('purchase_order_expire_at');

        if (!$orderMeta) {
            return $this->redirect($this->generateUrl('stock_purchase_order_item_list', ['id' => $id]));
        }

        /** @var RedisClient $redisClient **/
        $redisClient = $this->get('snc_redis.default');

        $redisClient->set($key = 'purchase_order:'.$id, serialize($orderMeta));
        $redisClient->expireat($key, (new \DateTime($expireAt))->getTimestamp());
        $this->get('session')->remove('order_meta');

        return $this->redirect($this->generateUrl('stock_purchase_order_item_xml_check', ['id' => $id]));
    }

    /**
     * @Route("/{id}/xml/deny", name="stock_purchase_order_item_xml_deny")
     * @Method({"GET"})
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_CSV")
     * @param int $id
     *
     * @throws \Exception
     * @return array
     */
    public function denyAction($id)
    {
        /** @var RedisClient $redisClient **/
        $redisClient = $this->get('snc_redis.default');

        $redisClient->del('purchase_order:'.$id);
        $this->get('session')->remove('order_meta');

        return $this->redirect($this->generateUrl('stock_purchase_order_item_list', ['id' => $id]));
    }

    /**
     * Generate a form to \files action
     *
     * @Route("/{id}/xml/accept", name="stock_purchase_order_item_xml_accept")
     * @Method({"GET"})
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_CSV")
     * @Template()
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     *
     * @throws \Exception
     * @return array
     */
    public function acceptAction(Request $request, $id)
    {
        /** @var RedisClient $redisClient **/
        $redisClient = $this->get('snc_redis.default');

        $orderMeta = unserialize($redisClient->get($key = 'purchase_order:'.$id));

        if (!$orderMeta) {
            return $this->redirect($this->generateUrl('stock_purchase_order_item_list', ['id' => $id]));
        }

        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        try {
            $entityManager->getConnection()->beginTransaction();

            $orderRequest = $entityManager->getRepository('NatueStockBundle:OrderRequest')->findOneById($orderMeta['order_request']);
            $purchaseOrder = $entityManager->getRepository('NatueStockBundle:PurchaseOrder')->findOneById($id);
            /* @var \Natue\Bundle\StockBundle\Service\PurchaseOrderItemXml */
            $purchaseOrderItemXmlManager =$this->get('natue.stock.purchaseorder.item.xml');

            $purchaseOrder->setOrderRequest($orderRequest);
            $entityManager->merge($purchaseOrder);
            $entityManager->flush();

            foreach ($orderMeta['products'] as $item) {
                $purchaseOrderItemXmlManager->insertItem($item, $purchaseOrder);
            }
            $entityManager->clear();

            $this->get('session')->getFlashBag()->add('success', 'Xml Imported');
            $this->get('session')->remove('order_meta');
            $redisClient->del($key);

            $entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();

            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('stock_purchase_order_item_list', ['id' => $id]));
    }
}
