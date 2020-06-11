<?php

namespace Natue\Bundle\ShippingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Doctrine\ORM\QueryBuilder;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use SebastianBergmann\Exporter\Exception;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Natue\Bundle\ShippingBundle\Form\Model\ExplicitPickingOrders as ExplicitPickingOrdersModel;
use Natue\Bundle\ShippingBundle\Form\Type\ExplicitPickingOrders as ExplicitPickingOrdersForm;
use Natue\Bundle\ShippingBundle\Form\Model\NumeralPickingOrders as NumeralPickingOrdersModel;
use Natue\Bundle\ShippingBundle\Form\Type\NumeralPickingOrders as NumeralPickingOrdersForm;
use Natue\Bundle\ShippingBundle\Form\Model\OrderIncrementIdLookup as OrderIncrementIdLookupModel;
use Natue\Bundle\ShippingBundle\Form\Type\OrderIncrementIdLookup as OrderIncrementIdLookupForm;
use Natue\Bundle\ShippingBundle\Form\Model\ProductBarcode as ProductBarcodeModel;
use Natue\Bundle\ShippingBundle\Form\Type\ProductBarcode as ProductBarcodeForm;
use Natue\Bundle\ShippingBundle\Service\PickingManager;
use Natue\Bundle\ShippingBundle\Service\ScannerStorage;
use Natue\Bundle\StockBundle\Service\StockItemManager;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Natue\Bundle\ZedBundle\Service\HttpClient;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;

/**
 * @Route("/picking")
 */
class PickingController extends Controller
{
    /**
     * @Route("/prepare", name="shipping_picking_prepare")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param Request $request
     *
     * @return array
     */
    public function prepareAction(Request $request)
    {
        return [
            'explicitForm' => $this->createForm(new ExplicitPickingOrdersForm())->createView(),
            'numeralForm' => $this->createForm(new NumeralPickingOrdersForm())->createView(),
        ];
    }

    /**
     * @Route("/post-orders-list", name="shipping_picking_post_orders_list")
     * @Method({"POST"})
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function postOrdersListAction(Request $request)
    {
        $pickingOrders = new ExplicitPickingOrdersModel();
        $form = $this->createForm(new ExplicitPickingOrdersForm(), $pickingOrders);

        $form->submit($request);

        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        try {
            $entityManager->getConnection()->beginTransaction();

            if (!$form->isValid()) {
                throw new \Exception('Error on form submission');
            }

            /** @var PickingManager $pickingManager */
            $pickingManager = $this->get('natue.shipping.picking_manager');

            $shippingPickingList = $pickingManager
                ->tryBuildShippingPickingListForOrderIdsList($pickingOrders->getOrdersList());

            $downloadUrl = $this->generateUrl(
                'shipping_download_picking_list',
                ['shippingPickingListId' => $shippingPickingList->getId()]
            );

            $successMessage = $this->get('translator')->trans(
                'Picking list number %id% created! <a href="%url%">Click here</a> to download it.',
                [
                    '%id%' => $shippingPickingList->getId(),
                    '%url%' => $downloadUrl
                ]
            );

            $this->get('session')->getFlashBag()->add('success', $successMessage);

            $failedOrders = $pickingManager->generatePdfFilesForPickingList($shippingPickingList);
            $pickingManager->removeOrdersOfPickingList($failedOrders);

            $entityManager->getConnection()->commit();

            return $this->redirect($this->generateUrl('shipping_picking_list'));
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();

            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            return $this->redirect($this->generateUrl('shipping_picking_prepare'));
        }
    }

    /**
     * @Route("/post-orders-amount", name="shipping_picking_post_orders_amount")
     * @Method({"POST"})
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function postOrdersAmountAction(Request $request)
    {
        $pickingOrders = new NumeralPickingOrdersModel();
        $form = $this->createForm(new NumeralPickingOrdersForm(), $pickingOrders);

        $form->submit($request);

        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        try {
            $entityManager->getConnection()->beginTransaction();

            if (!$form->isValid()) {
                throw new \Exception('Error on form submission');
            }

            /** @var PickingManager $pickingManager */
            $pickingManager = $this->get('natue.shipping.picking_manager');

            $shippingPickingList = $pickingManager->tryToBuildShippingPickingListForProvider(
                $pickingOrders->getLogisticsProvider(),
                $pickingOrders->getOrdersAmount(),
                $pickingOrders->getMonoSku()
            );

            if (!$shippingPickingList) {
                throw new \Exception('Not possible to generate picking list, no orders to collect');
            }

            $downloadUrl = $this->generateUrl(
                'shipping_download_picking_list',
                ['shippingPickingListId' => $shippingPickingList->getId()]
            );

            $successMessage = $this->get('translator')->trans(
                'Picking list number %id% created! <a href="%url%">Click here</a> to download it.',
                [
                    '%id%' => $shippingPickingList->getId(),
                    '%url%' => $downloadUrl
                ]
            );

            $this->get('session')->getFlashBag()->add('success', $successMessage);

            $failedOrders = $pickingManager->generatePdfFilesForPickingList(
                $shippingPickingList,
                $pickingOrders->getMonoSku()
            );

            $pickingManager->removeOrdersOfPickingList($failedOrders);

            $entityManager->getConnection()->commit();

            return $this->redirect($this->generateUrl('shipping_picking_list'));
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();

            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            return $this->redirect($this->generateUrl('shipping_picking_prepare'));
        }
    }

    /**
     * @Route("/list", name="shipping_picking_list")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param Request $request
     *
     * @return array
     */
    public function listAction(Request $request)
    {
        /** @var PickingManager $pickingManager */
        $pickingManager = $this->get('natue.shipping.picking_manager');

        $queryBuilder = $pickingManager->getShippingPickingListQueryBuilder()
            ->orderBy('shippingPickingList.createdAt', 'DESC');

        /** @var \Natue\Bundle\ShippingBundle\Grid\ShippingPickingListGrid $grid */
        $grid = $this->get('pedroteixeira.grid')
            ->createGrid('\Natue\Bundle\ShippingBundle\Grid\ShippingPickingListGrid');
        $grid->setQueryBuilder($queryBuilder);

        if ($grid->isResponseAnswer()) {
            return $grid->render();
        }

        return [
            'grid' => $grid->render()
        ];
    }

    /**
     * @Route("/regenerate-lists/{shippingPickingListId}", name="shipping_picking_regenerate_lists")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param Request $request
     * @param int $shippingPickingListId
     *
     * @return array
     */
    public function regenerateListsAction(Request $request, $shippingPickingListId)
    {
        /** @var PickingManager $pickingManager */
        $pickingManager = $this->get('natue.shipping.picking_manager');

        /** @var \Natue\Bundle\ShippingBundle\Entity\ShippingPickingList $shippingPickingList */
        $shippingPickingList = $pickingManager->getShippingPickingListById($shippingPickingListId);

        if ($shippingPickingList) {
            $pickingManager->generatePdfFilesForPickingList($shippingPickingList);
            $this->get('session')->getFlashBag()->add('success', 'ShippingPickingList PDFs has been regenerated.');
        } else {
            $this->get('session')->getFlashBag()->add('danger', 'ShippingPickingList not found.');
        }

        return $this->redirect($this->generateUrl('shipping_picking_list'));
    }

    /**
     * @Route("/find-order-by-increment-id", name="shipping_picking_find_order_by_increment_id")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param Request $request
     *
     * @return array
     */
    public function findOrderByIncrementIdAction(Request $request)
    {
        $orderIncrementIdLookup = new OrderIncrementIdLookupModel();
        $form = $this->createForm(new OrderIncrementIdLookupForm(), $orderIncrementIdLookup);

        if ($request->isMethod('post')) {

            $form->submit($request);

            try {
                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                /** @var PickingManager $pickingManager */
                $pickingManager = $this->get('natue.shipping.picking_manager');

                /** @var \Natue\Bundle\ZedBundle\Entity\ZedOrder $order */
                $order = $pickingManager->findOrderByIncrementId($orderIncrementIdLookup->getIncrementId());

                if (!$order) {
                    throw new \Exception('Order not found.');
                }

                $redirectUrl = $this->generateUrl(
                    'shipping_picking_check_order_products',
                    [
                        'orderId' => $order->getId()
                    ]
                );

                return $this->redirect($redirectUrl);
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/check-order-products/{orderId}", name="shipping_picking_check_order_products")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param Request $request
     * @param int $orderId
     *
     * @return array
     */
    public function checkOrderProductsAction(Request $request, $orderId)
    {
        /** @var PickingManager $pickingManager */
        $pickingManager = $this->get('natue.shipping.picking_manager');

        /** @var ZedOrder $order */
        $order = $pickingManager->findOrderById($orderId);

        if (!$order) {
            $this->get('session')->getFlashBag()->add('danger', 'Order not found.');
            return $this->redirect($this->generateUrl('shipping_picking_find_order_by_increment_id'));
        }

        if (!$pickingManager->isReadyToPick($order)) {
            $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('Order is not waiting for picking'));
            return $this->redirect($this->generateUrl('shipping_picking_find_order_by_increment_id'));
        }

        /** @var ScannerStorage $scannerStorage */
        $scannerStorage = $this->get('natue.shipping.scanner_storage');

        $barcode = new ProductBarcodeModel();
        $form = $this->createForm(
            new ProductBarcodeForm(
                $order,
                $scannerStorage->getPickingObservationReadStatus($order->getId())
            ),
            $barcode
        );

        return [
            'scannedItems' => $scannerStorage->getScannedItems($orderId),
            'form' => $form->createView(),
            'order' => $order,
            'conferredItems' => $scannerStorage->getTotalScannedItems($orderId),
            'totalItems' => $pickingManager->getTotalItemsForZedOrder($order),
        ];
    }

    /**
     * @Route("/validate-order-product-barcode/{orderId}", name="shipping_picking_validate_order_product_barcode")
     * @Method({"POST"})
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param Request $request
     * @param int $orderId
     *
     * @return RedirectResponse
     */
    public function validateOrderProductBarcodeAction(Request $request, $orderId)
    {
        /** @var PickingManager $pickingManager */
        $pickingManager = $this->get('natue.shipping.picking_manager');

        /** @var ZedOrder $order */
        $order = $pickingManager->findOrderById($orderId);
        if (!$order) {
            $this->get('session')->getFlashBag()->add('danger', 'Order not found.');

            return $this->redirect($this->generateUrl('shipping_picking_find_order_by_increment_id'));
        }

        /** @var ScannerStorage $scannerStorage */
        $scannerStorage = $this->get('natue.shipping.scanner_storage');

        $barcode = new ProductBarcodeModel();
        $form = $this->createForm(
            new ProductBarcodeForm(
                $order,
                $scannerStorage->getPickingObservationReadStatus($order->getId())
            ),
            $barcode
        );

        $form->submit($request);

        try {
            if (!$form->isValid()) {
                throw new \Exception('Error on form submission');
            }

            if (!$pickingManager->isBarcodeWithinOrder($barcode->getCode(), $order)) {
                throw new \Exception('Barcode is not within Order');
            }

            if (!$pickingManager->isElegibleToValidate($barcode->getCode(), $order, $scannerStorage)) {
                throw new \Exception('Barcode already checked');
            }

            if (!$pickingManager->isBarcodeHasStockStatus($barcode->getCode(), $order)) {
                throw new \Exception('Stock Item has wrong status');
            }

            if ($barcode->getPickingObservation()) {
                $scannerStorage->setPickingObservationReadStatus($orderId);
            }
            
            $scannerStorage->addItemBarcode($orderId, $barcode->getCode());
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('shipping_picking_check_order_products', ['orderId' => $orderId]));
    }

    /**
     * @Route("/confirm-order-checking/{orderId}", name="shipping_picking_confirm_order_checking")
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     * @Template()
     * @param int $orderId
     * @return RedirectResponse
     */
    public function confirmOrderCheckingAction($orderId)
    {
        /** @var PickingManager $pickingManager */
        $pickingManager = $this->get('natue.shipping.picking_manager');

        /** @var ZedOrder $order */
        $order = $pickingManager->findOrderById($orderId);

        if (!$order) {
            $this->get('session')->getFlashBag()->add('danger', 'Order not found.');
            return $this->redirect($this->generateUrl('shipping_picking_find_order_by_increment_id'));
        }

        $currentPickingConferenceUrl = $this->generateUrl(
            'shipping_picking_check_order_products',
            ['orderId' => $orderId]
        );

        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        try {
            $entityManager->getConnection()->beginTransaction();

            /** @var ScannerStorage $scannerStorage */
            $scannerStorage = $this->get('natue.shipping.scanner_storage');
            $barcodesCount = $scannerStorage->getScannedItems($orderId);

            $errors = $pickingManager->validateOrderBarcodesCount($order, $barcodesCount);

            if (!empty($errors)) {
                $this->get('session')->getFlashBag()->add(
                    'errorMessage',
                    $this->get('translator')->trans('There are missing items')
                );

                foreach ($errors['missingItems'] as $item) {
                    $this->get('session')->getFlashBag()->add(
                        'missingItems',
                        $this->get('translator')->trans(
                            "%position% - %name%",
                            [
                                '%position%' => $item->getStockPosition()->getName(),
                                '%name%' => $item->getZedProduct()->getName(),
                            ]
                        )
                    );
                }

                $entityManager->getConnection()->rollback();
                $entityManager->close();

                return $this->redirect($currentPickingConferenceUrl);
            }

            /** @var StockItemManager $stockItemManager */
            $stockItemManager = $this->get('natue.stock.item.manager');

            $stockItemManager->markZedOrderAsPicked($order, $this->container->get('event_dispatcher'));

            /** @var HttpClient $zedHttpClient */
            $zedHttpClient = $this->get('natue.zed.http_client');

            $isResponseSuccessful = $zedHttpClient->triggerPickedAllItemsForOrderId($orderId);

            if (!$isResponseSuccessful) {
                throw new \Exception('ZED order state update failure');
            }

            $isResponseSuccessful = $zedHttpClient->createInvoiceForOrderId($orderId);

            if (!$isResponseSuccessful) {
                throw new \Exception('ZED order state create_invoice update failure');
            }

            $entityManager->getConnection()->commit();
            $scannerStorage->clearScannedItems($orderId);

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('Order checked')
            );

            return $this->redirect($this->generateUrl('shipping_picking_find_order_by_increment_id'));
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());

            return $this->redirect($currentPickingConferenceUrl);
        }
    }

    /**
     * @Route("/cancel-order-checking/{orderId}", name="shipping_picking_cancel_order_checking")
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param int $orderId
     *
     * @return RedirectResponse
     */
    public function cancelOrderCheckingAction($orderId)
    {
        /** @var PickingManager $pickingManager */
        $pickingManager = $this->get('natue.shipping.picking_manager');

        /** @var ZedOrder $order */
        $order = $pickingManager->findOrderById($orderId);
        if (!$order) {
            $this->get('session')->getFlashBag()->add('danger', 'Order not found.');

            return $this->redirect($this->generateUrl('shipping_picking_find_order_by_increment_id'));
        }

        /** @var ScannerStorage $scannerStorage */
        $scannerStorage = $this->get('natue.shipping.scanner_storage');

        $scannerStorage->clearScannedItems($orderId);

        $this->get('session')->getFlashBag()->add('success', 'Checking order process canceled.');

        return $this->redirect($this->generateUrl('homepage'));
    }
}
