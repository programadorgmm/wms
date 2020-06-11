<?php

namespace Natue\Bundle\PdfBundle\Tests\Service;

use Hackzilla\BarcodeBundle\Utility\Barcode;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\PdfBundle\Service\BarcodeHandler;

/**
 * BarcodeHandler service test
 */
class BarcodeHandlerTest extends WebTestCase
{
    const VALID_CODE_ID_1 = 1;
    const VALID_CODE_ID_2 = 123456789;

    const VALID_CODE_BARCODE_STRING_1 = "000000000001";
    const VALID_CODE_BARCODE_STRING_2 = "000123456789";

    /**
     * Test the method generateBarcode
     *
     * @return void
     */
    public function testGenerateBarcode()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $barcodeHandler = $this->getMockBuilder('Natue\Bundle\PdfBundle\Service\BarcodeHandler')
            ->setConstructorArgs([$container])
            ->setMethods(['transformCodeToBarcodeString', 'getTemporaryFileName', 'saveBarcode'])
            ->getMock();

        $barcodeHandler->expects($this->once())
            ->method('transformCodeToBarcodeString')
            ->with(1)
            ->will($this->returnValue(self::VALID_CODE_BARCODE_STRING_1));

        $barcodeHandler->expects($this->once())
            ->method('getTemporaryFileName')
            ->with(self::VALID_CODE_BARCODE_STRING_1)
            ->will($this->returnValue(1));
        $barcodeHandler->expects($this->once())
            ->method('saveBarcode')
            ->will($this->returnValue(1));

        $this->assertEquals(
            1,
            $barcodeHandler->generateBarcode(1)
        );
    }

    /**
     * Test the method transformCodeToBarcodeString
     *
     * @return void
     */
    public function testTransformCodeToBarcodeString()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $barcodeHandler = $this->getMockBuilder('Natue\Bundle\PdfBundle\Service\BarcodeHandler')
            ->setConstructorArgs([$container])
            ->getMock();

        $this->assertEquals(
            self::VALID_CODE_BARCODE_STRING_1,
            $this->invokeMethod(
                $barcodeHandler,
                "transformCodeToBarcodeString",
                [self::VALID_CODE_ID_1]
            )
        );
        $this->assertEquals(
            self::VALID_CODE_BARCODE_STRING_2,
            $this->invokeMethod(
                $barcodeHandler,
                "transformCodeToBarcodeString",
                [self::VALID_CODE_ID_2]
            )
        );
    }

    /**
     * We check that if the temporary file name path contains the folder of the temporary folder
     * and php_barcode_ and the barcode string
     *
     * @return void
     */
    public function testGetTemporaryFileName()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $barcodeHandler = $this->getMockBuilder('Natue\Bundle\PdfBundle\Service\BarcodeHandler')
            ->setConstructorArgs([$container])
            ->getMock();

        $temporaryFolder = sys_get_temp_dir();
        $filePathBegin   = $temporaryFolder . DIRECTORY_SEPARATOR . "php_barcode_";

        $this->assertStringStartsWith(
            $filePathBegin . self::VALID_CODE_BARCODE_STRING_1,
            $this->invokeMethod($barcodeHandler, "getTemporaryFileName", [self::VALID_CODE_BARCODE_STRING_1])
        );

        $this->assertStringStartsWith(
            $filePathBegin . self::VALID_CODE_BARCODE_STRING_2,
            $this->invokeMethod($barcodeHandler, "getTemporaryFileName", [self::VALID_CODE_BARCODE_STRING_2])
        );
    }

    /**
     * Test the method saveBarcode
     *
     * @return void
     */
    public function testSaveBarcode()
    {
        $barcode = $this->getMockBuilder('Hackzilla\BarcodeBundle\Utility\Barcode')
            ->disableOriginalConstructor()
            ->setMethods(['save'])
            ->getMock();

        $barcode->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $barcodeHandler = $this->getMockBuilder('Natue\Bundle\PdfBundle\Service\BarcodeHandler')
            ->setConstructorArgs([$container])
            ->setMethods(['getBarcodeUtility'])
            ->getMock();

        $barcodeHandler->expects($this->once())
            ->method('getBarcodeUtility')
            ->will($this->returnValue($barcode));

        $this->invokeMethod($barcodeHandler, 'saveBarcode', ['test', 123]);
    }

    /**
     * Test the method getBarcodeUtility
     *
     * @return void
     */
    public function testGetBarcodeUtility()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $barcodeHandler = new BarcodeHandler($container);

        $getBarcodeUtilityMethod = $this->invokeMethod($barcodeHandler, 'getBarcodeUtility');

        $this->assertTrue($getBarcodeUtilityMethod instanceof Barcode);
    }
}
