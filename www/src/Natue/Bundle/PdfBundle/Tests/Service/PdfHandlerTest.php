<?php

namespace Natue\Bundle\PdfBundle\Tests\Service;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * PdfHandler service test
 */
class PdfHandlerTest extends WebTestCase
{
    /**
     * Test the constructor of PdfHandler
     *
     * @return void
     */
    public function testConstructor()
    {
        $templating = $this->getMockBuilder('Symfony\Bundle\TwigBundle\TwigEngine')
            ->disableOriginalConstructor()
            ->getMock();

        $knpSnappy = $this->getMockBuilder('Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator')
            ->disableOriginalConstructor()
            ->getMock();

        $barcodeHandler = $this->getMockBuilder('Natue\Bundle\PdfBundle\Service\BarcodeHandler')
            ->disableOriginalConstructor()
            ->getMock();

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $pdfHandler = $this->getMockBuilder('Natue\Bundle\PdfBundle\Service\PdfHandler')
            ->setConstructorArgs([$templating, $knpSnappy, $barcodeHandler, $container])
            ->getMock();

        $this->assertAttributeEquals($templating, "templating", $pdfHandler);
        $this->assertAttributeEquals($knpSnappy, "knpSnappy", $pdfHandler);
        $this->assertAttributeEquals($barcodeHandler, "barcodeHandler", $pdfHandler);
        $this->assertAttributeEquals($container, "container", $pdfHandler);
    }

    /**
     * Test the method generatePdfForPurchaseOrderVolume
     *
     * @return void
     */
    public function testGeneratePdfForPurchaseOrderVolume()
    {
        $templating = $this->getMockBuilder('Symfony\Bundle\TwigBundle\TwigEngine')
            ->disableOriginalConstructor()
            ->setMethods(['render'])
            ->getMock();
        $templating->expects($this->once())
            ->method('render')
            ->will($this->returnValue("template"));

        $knpSnappy = $this->getMockBuilder('Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator')
            ->disableOriginalConstructor()
            ->setMethods(['getOutputFromHtml'])
            ->getMock();
        $knpSnappy->expects($this->once())
            ->method('getOutputFromHtml')
            ->will($this->returnValue("pdf data"));

        $barcodeHandler = $this->getMockBuilder('Natue\Bundle\PdfBundle\Service\BarcodeHandler')
            ->disableOriginalConstructor()
            ->setMethods(['generateBarcode'])
            ->getMock();
        $barcodeHandler->expects($this->once())
            ->method('generateBarcode')
            ->will($this->returnValue("barcode"));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $pdfHandler = $this->getMockBuilder('Natue\Bundle\PdfBundle\Service\PdfHandler')
            ->setConstructorArgs([$templating, $knpSnappy, $barcodeHandler, $container])
            ->setMethods(null) // WORK AROUND for phpunit bug
            ->getMock();

        $this->assertEquals("pdf data", $pdfHandler->generatePdfForPurchaseOrderVolume(1, 1));
    }
}
