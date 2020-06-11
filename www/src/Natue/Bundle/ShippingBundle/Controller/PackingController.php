<?php

namespace Natue\Bundle\ShippingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\ORM\QueryBuilder;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use SebastianBergmann\Exporter\Exception;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Natue\Bundle\ZedBundle\Service\HttpClient;
use Natue\Bundle\ShippingBundle\Entity\ShippingPackage;
use Natue\Bundle\ShippingBundle\Service\PackingManager;
use Natue\Bundle\ShippingBundle\Service\PickingManager;
use Natue\Bundle\ShippingBundle\Form\Type\PackingOrders as PackingOrdersForm;
use Natue\Bundle\ShippingBundle\Form\Type\LogisticProviderAndPackage as LogisticProviderAndPackageForm;
use Natue\Bundle\ShippingBundle\Form\Type\OrderSelection as OrderSelectionForm;
use Natue\Bundle\ShippingBundle\Form\Type\VolumeContents as VolumeContentsForm;
use Natue\Bundle\ShippingBundle\Form\Type\VolumeOrder as VolumeOrderForm;
use Natue\Bundle\ShippingBundle\Exception\ExpeditionCheckNeededException;

/**
 * Packing controller
 *
 * @Route("/packing")
 */
class PackingController extends Controller
{
    /**
     * @Route("/", name="shipping_packing_items")
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    public function packingOrdersAction(Request $request)
    {
        return [
            'form' => $this->createForm(new PackingOrdersForm())->createView(),
        ];
    }

    /**
     * @Route("/confirm-packed-order", name="shipping_confirm_packed_order")
     * @Secure(roles="ROLE_ADMIN")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function confirmPackedOrderAction(Request $request)
    {
        $packedOrderForm = $this->createForm(new PackingOrdersForm());
        $packedOrderForm->bind($request);

        try {
            $pickingManager = $this->get('natue.shipping.picking_manager');
            $zedOrder = $pickingManager->findOrderByIncrementId($packedOrderForm['order_increment_id']->getData());

            if (!$zedOrder) {
                throw new \Exception("Order {$packedOrderForm['order_increment_id']->getData()} not found");
            }

            $packingManager = $this->get('natue.shipping.packing_manager');
            $packingManager->registerPackingZedOrderForUser($zedOrder, $this->getUser());

            $this->get('session')->getFlashBag()->add('success', 'Items has been packed');
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('shipping_packing_items'));
    }

    /**
     * @Route("/select-provider-and-package", name="shipping_packing_select_provider_and_package")
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function selectProviderAndPackageAction(Request $request)
    {
        $session = $request->getSession();

        return [
            'form'           => $this->createForm(new LogisticProviderAndPackageForm())->createView(),
            'packageCounter' => $session->get('packageCounter'),
            'expeditionList' => $session->get('expeditionList')
        ];
    }

    /**
     * @Route("/expedition-control", name="shipping_packing_expedition_control")
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function expeditionControlAction(Request $request)
    {
        $packingManager = $this->get('natue.shipping.packing_manager');

        return [
            'orders' => $packingManager->getOrdersReadyForShipping()
        ];
    }

    /**
     * @Route("/logistic-provider/expedition-orders", name="shipping_packing_logistic-_rovider_expedition_orders")
     * @Method({"PUT"})
     * @Secure(roles="ROLE_ADMIN")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function expeditionOrdersAction(Request $request)
    {
        $logisticsProviderId  = $request->get('id');
        $packingManager       = $this->get('natue.shipping.packing_manager');

        $orders = $packingManager->getOrdersReadyForShippingByLogisticsProvider(
            $logisticsProviderId
        );

        $redis = $this->container->get('snc_redis.default');

        foreach ($orders as $value) {
            $cached = $redis->set(
                'expeditionOrders:'.$logisticsProviderId.':'.$value['orderId'],
                json_encode($value)
            );
        }

        if ($cached) {
            $response = [
                'message' => "Successfully issued",
                'status'  => JsonResponse::HTTP_OK
            ];
        } else {
            $response = [
                'message' => 'Error issued',
                'status'  => JsonResponse::HTTP_BAD_REQUEST
            ];
        }

        $jsonResponse = new JsonResponse($response);
        $jsonResponse->setStatusCode($response['status']);

        return $jsonResponse;
    }

    /**
     * @Route("/choose-an-order", name="shipping_packing_choose_order")
     * @Method({"POST"})
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function chooseProviderAndPackageAction(Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getConnection()->beginTransaction();

        try {
            $expeditionForm = $this->createForm(new LogisticProviderAndPackageForm());
            $expeditionForm->bind($request);

            if (!$expeditionForm->isValid()) {
                throw new \Exception('Required data not provided within url.');
            }

            $orderIncrementId       = strtoupper($expeditionForm['order_increment_id']->getData());
            $packageId              = $expeditionForm['package_id']->getData();
            $shippingTrackCode      = strtoupper($expeditionForm['shipping_track_code']->getData());
            $isRecheck              = $expeditionForm['order_recheck']->getData();
            $logisticsProvider      = $expeditionForm['logistics_provider_id']->getData();

            /** @var PickingManager $pickingManager */
            $pickingManager = $this->get('natue.shipping.picking_manager');

            /** @var ZedOrder $zedOrder */
            $zedOrder = $pickingManager->findOrderByIncrementId($orderIncrementId);

            if (!$zedOrder) {
                throw new \Exception("Order {$orderIncrementId} not found");
            }

            $stockItemManager = $this->get('natue.stock.item.manager');
            /** @var PackingManager $packingManager */
            $packingManager = $this->get('natue.shipping.packing_manager');
            $packingManager->validateInitialSelection($zedOrder);

            /** @var ShippingPackage $package */
            $package = $packingManager->findPackageById($packageId);

            if (!$package) {
                throw new Exception("Package {$packageId} not found");
            }

            $orderAssignData = [
                'orderIncrementId'  => $orderIncrementId,
                'packageId'         => $packageId,
                'trackingCode'      => $shippingTrackCode,
                'isRecheck'         => $isRecheck,
                'logisticsId'       => $logisticsProvider,
            ];

            $packingManager->tryAssignStockItemsToNewVolume($orderAssignData);
            $stockItemManager->markZedOrderAsReadyForShipping($zedOrder);

            $customeInformation = $packingManager->getCustomerName($orderIncrementId);

            $packageCounter = $packingManager->handlePackageCounterInSession(
                $request->getSession(),
                $package
            );

            $expeditionList = $packingManager->handlerExpeditionList(
                $request->getSession(),
                $orderIncrementId,
                $package,
                $customeInformation
            );

            $entityManager->getConnection()->commit();

            $response = [
                'message'        => "Successfully issued",
                'status'         => JsonResponse::HTTP_OK,
                'packageCounter' => $packageCounter,
                'expeditionList' => $expeditionList,
            ];
        } catch (ExpeditionCheckNeededException $ene) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();

            $response = [
                'message'       => $ene->getMessage(),
                'status'        => JsonResponse::HTTP_BAD_REQUEST,
                'recheckOrder'  => $orderIncrementId
            ];
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            $response = ['message' => $e->getMessage(), 'status' => JsonResponse::HTTP_BAD_REQUEST];
        }

        $jsonResponse = new JsonResponse($response);
        $jsonResponse->setStatusCode($response['status']);

        return $jsonResponse;
    }
}
