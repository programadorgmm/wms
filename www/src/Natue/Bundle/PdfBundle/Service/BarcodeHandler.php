<?php

namespace Natue\Bundle\PdfBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Hackzilla\BarcodeBundle\Utility\Barcode;

/**
 * Barcode Generator
 */
class BarcodeHandler
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Generate a barcode picture from a code (e.g. an id)
     * Return the path to the barcode picture.
     *
     * @param String $code
     *
     * @return String
     */
    public function generateBarcode($code)
    {
        $barcodeString = $this->transformCodeToBarcodeString($code);

        $barcodePath = $this->getTemporaryFileName($barcodeString);

        $this->saveBarcode($barcodePath, $barcodeString);

        return $barcodePath;
    }

    /**
     * Generate a valid code (a barcode string) for the barcode from a code (e.g. an id)
     * To do that we add 0 in front of the code if the length is < 12 digits
     * A barcode contains 12 digits + 1 digit for the checksum
     *
     * @param String $code
     *
     * @return String
     */
    protected function transformCodeToBarcodeString($code)
    {
        return str_pad($code, 12, "0", STR_PAD_LEFT);
    }

    /**
     * Generate a filename for the barcode in the temporary folder of the system
     * Return the path of the barcode
     *
     * @param String $barcodeString
     *
     * @return String
     */
    protected function getTemporaryFileName($barcodeString)
    {
        $fileName    = "php_barcode_" . $barcodeString . "_" . time() . ".png";
        $barcodePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;

        return $barcodePath;
    }

    /**
     * Generate a barcode from $barcodeString and save it in $barcodePath
     *
     * @param String $barcodeString
     * @param String $barcodePath
     */
    protected function saveBarcode($barcodePath, $barcodeString)
    {
        $barcode = $this->getBarcodeUtility();
        $barcode->setHeight(112);
        $barcode->setScale(5);

        $barcode->save($barcodeString, $barcodePath);
    }

    /**
     * @return Barcode
     */
    protected function getBarcodeUtility()
    {
        $genbarcodePath = $this->container->getParameter('genbarcode_path');

        $barcode = new Barcode();
        $barcode->setGenbarcodeLocation($genbarcodePath);
        $barcode->setEncoding(Barcode::ENCODING_128);

        return $barcode;
    }
}
