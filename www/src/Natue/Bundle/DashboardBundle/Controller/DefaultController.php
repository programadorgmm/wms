<?php

namespace Natue\Bundle\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * Default controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="dashboard")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        /** @var \Natue\Bundle\DashboardBundle\Service\DashboardHandler $dashboardService */
        $dashboardService = $this->get('natue.dashboard.handler');
        $dashboard        = $dashboardService->getDashboardList();

        $jsonResponse = new JsonResponse($dashboard);
        $jsonResponse->setStatusCode(200);

        return $jsonResponse;
    }
}
