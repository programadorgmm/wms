<?php

namespace Natue\Bundle\PdfBundle\Service;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Natuelabs;

use Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator;

/**
 * PdfGenerator
 */
class PdfHandler
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var TwigEngine
     */
    protected $templating;

    /**
     * @var LoggableGenerator
     */
    protected $knpSnappy;

    /**
     * @var BarcodeHandler
     */
    protected $barcodeHandler;

    /**
     * @param TwigEngine        $templating
     * @param LoggableGenerator $knpSnappy
     * @param BarcodeHandler    $barcodeHandler
     */
    public function __construct(
        TwigEngine $templating,
        LoggableGenerator $knpSnappy,
        BarcodeHandler $barcodeHandler,
        ContainerInterface $container
    ) {
        $this->templating     = $templating;
        $this->knpSnappy      = $knpSnappy;
        $this->barcodeHandler = $barcodeHandler;
        $this->container      = $container;
    }

    /**
     * Generate the pdf data from the path of the picture of the barcode and from the volumes total.
     * We need the volumes total to know how many times we will need to show the barcode.
     *
     * @param String $purchaseOrderId
     * @param String $volumesTotal
     *
     * @return String
     */
    public function generatePdfForPurchaseOrderVolume($purchaseOrderId, $volumesTotal)
    {
        $barcodePath = $this->barcodeHandler->generateBarcode($purchaseOrderId);
        $html        = $this->templating->render(
            'NatuePdfBundle:Generator/PurchaseOrder:volumes.html.twig',
            [
                'barcodeAddress' => $barcodePath,
                'volumesTotal'   => $volumesTotal
            ]
        );

        return $this->knpSnappy->getOutputFromHtml($html);
    }

    public function createShippingPickingListPdf($viewData)
    {
        $html = $this->templating->render(
            'NatuePdfBundle:Generator/Shipping:picking-list.html.twig',
            $viewData
        );

        $output = $this->container->get('kernel')->getRootDir()
                . $this->container->getParameter('shipping_picking_list_path')
                . $viewData['pickingListId'] . '.pdf';

        $this->knpSnappy->generateFromHtml($html, $output, [], true);
    }

    public function createShippingPickingListPdfForMonoSku($viewData)
    {
        $html = $this->templating->render(
            'NatuePdfBundle:Generator/Shipping:picking-list-mono-sku.html.twig',
            $viewData
        );

        $output = $this->container->get('kernel')->getRootDir()
                . $this->container->getParameter('shipping_picking_list_path')
                . $viewData['pickingListId'] . '.pdf';

        $this->knpSnappy->generateFromHtml($html, $output, [], true);
    }

    /**
     * @param $filename
     * @param $viewData
     */
    public function createExpeditionLabelsPdf($filename, $viewData)
    {
        $html = $this->templating->render(
            'NatuePdfBundle:Generator/Shipping:expedition-labels.html.twig',
            $viewData
        );

        $output = $this->container->get('kernel')->getRootDir()
                . $this->container->getParameter('pdf_expedition_labels_path')
                . $filename . '.pdf';

        $options = [
            'page-size'     => 'A7',
            'orientation'   => 'Landscape',
            'margin-top'    => 1,
            'margin-right'  => 1,
            'margin-bottom' => 1,
            'margin-left'   => 1,
        ];

        $this->knpSnappy->generateFromHtml($html, $output, $options, true);
    }

    /**
     * @param $filename
     * @param $viewData
     *
     * @return array
     */
    public function createInvoicesPdf($filename, $viewData)
    {
        $failedOrders = [];
        $region = $this->container->getParameter('aws.region');
        $environment = $this->container->getParameter('aws.environment');
        $credentials = [
            'key' => $this->container->getParameter('aws.key'),
            'secret' => $this->container->getParameter('aws.secret'),
        ];
        $bucket = 'natuelabs.taxman';

        $reader = new Natuelabs\Danphpe\Reader\S3($region, $environment, $credentials, $bucket);

        $path = $this->container->get('kernel')->getRootDir() . $this->container->getParameter('pdf_invoices_path');
        $storage = new Natuelabs\Danphpe\Storage\FileSystem($path);

        $invoiceList = [];
        foreach ($viewData['orders'] as $data) {
            try {
                $invoiceList[] = Natuelabs\Danphpe\PDF\merge\raw($reader->getContents($data['invoiceKey'] . '.pdf'));
            } catch (Natuelabs\Danphpe\Reader\Exceptions\DanfeNotFoundException $exception) {
                $failedOrders[] = $data['incrementId'];
            }
        }

        $filename .= '.pdf';
        $storage->save($filename, Natuelabs\Danphpe\PDF\merge($invoiceList));

        return $failedOrders;
    }
}
