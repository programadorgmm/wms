<?php

namespace Natue\Bundle\StockBundle\Tests\Controller;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * Purchase Order test
 */
class PurchaseOrderTest extends WebTestCase
{
    /**
     * Test the method listAction from PurchaseOrderController
     *
     * @return void
     */
    public function testListAction()
    {
        $client = self::$client;

        $crawler = $client->request('GET', self::$router->generate('stock_purchase_order_list'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('table.pedroteixeira-grid-table')->count());

        $client->request(
            'GET',
            self::$router->generate('stock_purchase_order_list'),
            [],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
    }

    /**
     * Test the method createAction from PurchaseOrderController
     *
     * @return void
     */
    public function testCreateAction()
    {
        $client      = self::$client;
        $zedSupplier = $this->zedSupplierFactory();

        $crawler = $client->request('GET', self::$router->generate('stock_purchase_order_create'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(8, $crawler->filterXPath('//input')->count());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_purchase_order[invoice_key]']            = 'Key #' . uniqid();
        $form['natue_stockbundle_purchase_order[volumes_total]']          = 25;
        $form['natue_stockbundle_purchase_order[cost_total]']             = 1010;
        $form['natue_stockbundle_purchase_order[zed_supplier]']           = $zedSupplier->getId();
        $form['natue_stockbundle_purchase_order[date_ordered]']           = '2014-08-07';
        $form['natue_stockbundle_purchase_order[date_expected_delivery]'] = '2014-08-20';
        $form['natue_stockbundle_purchase_order[date_actual_delivery]']   = '2014-08-22';

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Created') . '")')->count()
        );
    }

    /**
     * Test the method createAction with invalid arguments from PurchaseOrder
     *
     * @return void
     */
    public function testInvalidCreateAction()
    {
        $client = self::$client;

        // An invalid invoice key is a key that already exist (invoice key is unique)
        $purchaseOrder = $this->purchaseOrderFactory();
        self::$entityManager->persist($purchaseOrder);
        self::$entityManager->flush();
        $invalidInvoiceKey = $purchaseOrder->getInvoiceKey();

        $crawler = $client->request('GET', self::$router->generate('stock_purchase_order_create'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(8, $crawler->filterXPath('//input')->count());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_purchase_order[invoice_key]'] = $invalidInvoiceKey;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Invoice key already exists') . '")')->count()
        );
    }

    /**
     * Test the method updateAction from PurchaseOrderController
     *
     * @return void
     */
    public function testUpdateAction()
    {
        $client        = self::$client;
        $purchaseOrder = $this->purchaseOrderFactory();

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_purchase_order_update', ['id' => $purchaseOrder->getId()])
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(8, $crawler->filterXPath('//input')->count());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Update Purchase Order') . '")')->count()
        );

        $changedKey = 'Changed #' . uniqid();

        $form = $crawler->filterXpath('//button[@type="submit"]')->form();

        $form['natue_stockbundle_purchase_order[invoice_key]'] = $changedKey;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Updated') . '")')->count()
        );

        self::$entityManager->refresh($purchaseOrder);

        $this->assertEquals($changedKey, $purchaseOrder->getInvoiceKey());
    }

    /**
     * Test the method updateAction from PurchaseOrderController
     *
     * @return void
     */
    public function testEmptyUpdateAction()
    {
        $client = self::$client;

        $this->setExpectedException('Symfony\Component\Routing\Exception\MissingMandatoryParametersException');

        $client->request('GET', self::$router->generate('stock_purchase_order_update'));
    }

    /**
     * Test the method updateAction with invalid arguments from PurchaseOrder
     *
     * @return void
     */
    public function testInvalidUpdateAction()
    {
        $client = self::$client;

        // An invalid invoice key is a key that already exist (invoice key is unique)
        $purchaseOrder1 = $this->purchaseOrderFactory();
        $purchaseOrder2 = $this->purchaseOrderFactory();
        self::$entityManager->persist($purchaseOrder1);
        self::$entityManager->persist($purchaseOrder2);
        self::$entityManager->flush();
        $invalidInvoiceKey = $purchaseOrder2->getInvoiceKey();

        $wrongPurchaseOrderId = $this->generateWrongPurchaseOrderId();

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_purchase_order_update', ['id' => $wrongPurchaseOrderId])
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Not found') . '")')->count()
        );


        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_purchase_order_update', ['id' => $purchaseOrder1->getId()])
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(8, $crawler->filterXPath('//input')->count());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_purchase_order[invoice_key]'] = $invalidInvoiceKey;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Invoice key already exists') . '")')->count()
        );
    }

    /**
     * Test the method deleteAction from PurchaseOrderController
     *
     * @return void
     */
    public function testDeleteAction()
    {
        $client        = self::$client;
        $purchaseOrder = $this->purchaseOrderFactory();

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_purchase_order_delete', ['id' => $purchaseOrder->getId()])
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Deleted') . '")')->count()
        );
    }

    /**
     * Test the method deleteAction from PurchaseOrderController
     *
     * @return void
     */
    public function testEmptyDeleteAction()
    {
        $client = self::$client;

        $this->setExpectedException('Symfony\Component\Routing\Exception\MissingMandatoryParametersException');

        $client->request('GET', self::$router->generate('stock_purchase_order_delete'));
    }

    /**
     * Test the method deleteAction with invalid arguments from PurchaseOrderController
     *
     * @return void
     */
    public function testInvalidDeleteAction()
    {
        $client = self::$client;

        $wrongPurchaseOrderId = $this->generateWrongPurchaseOrderId();

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_purchase_order_delete', ['id' => $wrongPurchaseOrderId])
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Not found') . '")')->count()
        );

        $purchaseOrderItem = $this->purchaseOrderItemFactory();
        $purchaseOrder     = $this->purchaseOrderFactory();
        $purchaseOrderItem->setPurchaseOrder($purchaseOrder);

        self::$entityManager->persist($purchaseOrder);
        self::$entityManager->persist($purchaseOrderItem);
        self::$entityManager->flush();


        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_purchase_order_delete', ['id' => $purchaseOrder->getId()])
        );
        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' .
                self::$translator->trans('This purchase order contains purchase order items, delete is impossible.') .
                '")'
            )->count()
        );
    }

    /**
     * @return integer
     */
    private function generateWrongPurchaseOrderId()
    {
        $repository   = self::$entityManager->getRepository('NatueStockBundle:PurchaseOrder');
        $queryBuilder = $repository->createQueryBuilder('purchaseOrder')
            ->select('MAX(purchaseOrder.id)');

        return $queryBuilder->getQuery()->getSingleScalarResult() + 1;
    }
}
