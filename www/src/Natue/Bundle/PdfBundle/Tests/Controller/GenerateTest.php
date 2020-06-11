<?php

namespace Natue\Bundle\PdfBundle\Tests\Controller;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * Controller generate test
 */
class GenerateTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testGenerateAction()
    {
        $entity = $this->purchaseOrderFactory();

        $client = self::$client;

        $client->request(
            'GET',
            self::$router->generate(
                'pdf_generate_purchase_order_volume',
                [
                    'id'           => $entity->getId(),
                    'volumesTotal' => $entity->getVolumesTotal()
                ]
            )
        );

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/pdf'
            )
        );

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Disposition',
                'attachment; filename="PurchaseOrderVolumeBarcode.pdf"'
            )
        );
    }
}
