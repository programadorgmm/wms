<?php

namespace Natue\Bundle\ShippingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\SecurityExtraBundle\Annotation\Secure;

/**
 * Picking controller
 *
 * @Route("/download")
 */
class DownloadController extends Controller
{

    const SHIPPING_PICKING_LIST_DIR = '/data/pdf/ShippingPickingList/';
    const EXPEDITION_LABELS_DIR     = '/data/pdf/ExpeditionLabels/';
    const INVOICE_LABELS_DIR     = '/data/pdf/Invoices/';

    /**
     * @Route("/picking-list/{shippingPickingListId}", name="shipping_download_picking_list")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param Request $request
     * @param integer $shippingPickingListId
     *
     * @return BinaryFileResponse
     */
    public function pickingListAction($shippingPickingListId, Request $request)
    {
        $pdfPath = $this->getPickingListPdfPath($shippingPickingListId);

        return new BinaryFileResponse($pdfPath);
    }

    /**
     * @Route("/expedition-labels/{shippingPickingListId}", name="shipping_download_expedition_labels")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param Request $request
     * @param integer $shippingPickingListId
     *
     * @return BinaryFileResponse
     */
    public function expeditionLabelsAction($shippingPickingListId, Request $request)
    {
        $pdfPath = $this->getExpeditionLabelsPdfPath($shippingPickingListId);

        return new BinaryFileResponse($pdfPath);
    }

    /**
     * @Route("/invoices/{shippingPickingListId}", name="shipping_download_invoices")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_SHIPPING_PICKING_LIST")
     *
     * @param Request $request
     * @param integer $shippingPickingListId
     *
     * @return BinaryFileResponse
     */
    public function invoicesAction($shippingPickingListId, Request $request)
    {
        $pdfPath = $this->getInvoicesPdfPath($shippingPickingListId);

        return new BinaryFileResponse($pdfPath);
    }

    /**
     * @param $shippingPickingListId
     *
     * @return string
     */
    private function getPickingListPdfPath($shippingPickingListId)
    {
        $path = $this->container->get('kernel')->getRootDir()
              . self::SHIPPING_PICKING_LIST_DIR
              . $shippingPickingListId . '.pdf';

        return $path;
    }

    /**
     * @param $shippingPickingListId
     *
     * @return string
     */
    private function getExpeditionLabelsPdfPath($shippingPickingListId)
    {
        $path = $this->container->get('kernel')->getRootDir()
              . self::EXPEDITION_LABELS_DIR
              . $shippingPickingListId . '.pdf';

        return $path;
    }

    /**
     * @param $shippingPickingListId
     *
     * @return string
     */
    private function getInvoicesPdfPath($shippingPickingListId)
    {
        $path = $this->container->get('kernel')->getRootDir()
              . self::INVOICE_LABELS_DIR
              . $shippingPickingListId . '.pdf';

        return $path;
    }
}
