<?php

namespace Natue\Bundle\PdfBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Natue\Bundle\PdfBundle\Service\PdfHandler;

/**
 * Generator controller
 *
 * @Route("/generate")
 */
class GenerateController extends Controller
{
    /**
     * Purchase Order action
     *
     * @Route("/purchase-order-volume/{id}/{volumesTotal}", name="pdf_generate_purchase_order_volume")
     * @Template()
     * @Secure(roles="ROLE_ADMIN, ROLE_PDF_GENERATE_PURCHASE_ORDER_VOLUME")
     *
     * @param int $id
     * @param int $volumesTotal
     *
     * @return array
     */
    public function purchaseOrderVolumeAction($id, $volumesTotal)
    {
        /** @var PdfHandler $pdfHandlerService */
        $pdfHandlerService = $this->get('natue.pdf.handler');
        $pdfData           = $pdfHandlerService->generatePdfForPurchaseOrderVolume($id, $volumesTotal);

        return $this->renderPdf($pdfData, "PurchaseOrderVolumeBarcode");
    }

    /**
     * @param $data
     * @param $filename
     *
     * @return Response
     */
    private function renderPdf($data, $filename)
    {
        return new Response(
            $data,
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'. $filename .'.pdf"'
            ]
        );
    }
}
