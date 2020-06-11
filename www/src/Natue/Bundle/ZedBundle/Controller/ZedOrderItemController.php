<?php

namespace Natue\Bundle\ZedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Natue\Bundle\ZedBundle\Repository\ZedOrderItemRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * ZedOrderItem controller
 *
 * @Route("/zed-order-item")
 */
class ZedOrderItemController extends Controller
{
    /**
     *
     * @Route("/picking-list", name="picking_list")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return json
     */
    public function getPickingListAction(Request $request)
    {
        $logisticsProvider = $request->query->get('logisticsProvider');
        $zedOrderItem      = $this->getDoctrine()
                                  ->getRepository('NatueZedBundle:ZedOrderItem')
                                  ->getPickingListByLogisticProvider($logisticsProvider);

        $jsonResponse = new JsonResponse($zedOrderItem);
        $jsonResponse->setStatusCode(200);

        return $jsonResponse;
    }
}
