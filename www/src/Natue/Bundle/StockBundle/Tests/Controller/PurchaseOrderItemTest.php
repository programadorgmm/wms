<?php

namespace Natue\Bundle\StockBundle\Tests\Controller;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType;

/**
 * PurchaseOrderItem test
 */
class PurchaseOrderItemTest extends WebTestCase
{
    const FORM_COST        = 'natue_stockbundle_purchase_order_item[cost]';
    const FORM_ZED_PRODUCT = 'natue_stockbundle_purchase_order_item[zed_product]';
    const FORM_QUANTITY    = 'natue_stockbundle_purchase_order_item[quantity]';
    const CSV_HEADER       = 'SKU;COST;QUANTITY';

    /**
     * @param array $csvRows
     *
     * @return string
     */
    private function generateSampleCsvUploadedFile($csvRows)
    {
        $fileName = 'testingCsv_' . time() . '.csv';
        $csvPath  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
        $file     = fopen($csvPath, 'w');

        foreach ($csvRows as $line) {
            fputcsv($file, explode(';', $line), ';');
        }
        fclose($file);

        return $csvPath;
    }

    /**
     * Test the method listAction from PurchaseOrderItem
     *
     * @return void
     */
    public function testListAction()
    {
        $purchaseOrder = $this->purchaseOrderFactory();

        $client = self::$client;

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_purchase_order_item_list',
                [
                    'id' => $purchaseOrder->getId()
                ]
            )
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('table.pedroteixeira-grid-table')->count());

        $client->request(
            'GET',
            self::$router->generate(
                'stock_purchase_order_item_list',
                [
                    'id' => $purchaseOrder->getId()
                ]
            ),
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
     * Test the method listAction with invalid arguments from PurchaseOrderItem
     *
     * @return void
     */
    public function testInvalidListAction()
    {
        $client = self::$client;

        $wrongPurchaseOrderId = $this->generateWrongPurchaseOrderId();

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_purchase_order_item_list', ['id' => $wrongPurchaseOrderId])
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Purchase Order not found') . '")')->count()
        );
    }

    /**
     * Test the method createAction from PurchaseOrderItem
     *
     * @return void
     */
    public function testCreateAction()
    {
        $client = self::$client;

        $purchaseOrderId = $this->purchaseOrderFactory()->getId();
        $zedProductId    = $this->zedProductFactory()->getId();

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_purchase_order_item_create', ['id' => $purchaseOrderId])
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(4, $crawler->filterXPath('//input')->count());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form[self::FORM_COST]        = 10;
        $form[self::FORM_ZED_PRODUCT] = $zedProductId;
        $form[self::FORM_QUANTITY]    = 10;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Created') . '")')->count()
        );
    }

    /**
     * Test the method createAction with invalid arguments from PurchaseOrderItem
     *
     * @return void
     */
    public function testInvalidCreatedAction()
    {
        $client = self::$client;

        $repository           = self::$entityManager->getRepository('NatueStockBundle:PurchaseOrder');
        $queryBuilder         = $repository->createQueryBuilder('purchaseOrder')
            ->select('MAX(purchaseOrder.id)');
        $wrongPurchaseOrderId = $queryBuilder->getQuery()->getSingleScalarResult() + 1;

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_purchase_order_item_create', ['id' => $wrongPurchaseOrderId])
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Purchase Order not found') . '")')->count()
        );
    }

    /**
     * Test the method updateAction from PurchaseOrderItem.
     * We test that we can add 10 new items with the update method.
     * Then we test that we can remove 5 items with the update method.
     *
     * @return void
     */
    public function testUpdateAction()
    {
        $client = self::$client;

        $newZedProduct     = $this->zedProductFactory();
        $purchaseOrderItem = $this->purchaseOrderItemFactory();
        $purchaseOrder     = $purchaseOrderItem->getPurchaseOrder();
        $oldZedProduct     = $purchaseOrderItem->getZedProduct();

        $initialStatus = EnumPurchaseOrderItemStatusType::STATUS_INCOMING;

        $purchaseOrderItem->setStatus($initialStatus);
        self::$entityManager->persist($purchaseOrderItem);
        self::$entityManager->flush($purchaseOrderItem);

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_purchase_order_item_update',
                [
                    'purchaseOrderId' => $purchaseOrder->getId(),
                    'cost'            => $purchaseOrderItem->getCost(),
                    'sku'             => $oldZedProduct->getSku()
                ]
            )
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(4, $crawler->filterXPath('//input')->count());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Update Purchase Order Item') . '")')->count()
        );

        $form                  = $crawler->filterXpath('//button[@type="submit"]')->form();
        $changedCost           = 20;
        $changedZedProductId   = $newZedProduct->getId();
        $purchaseOrderItemList = self::$entityManager->getRepository('NatueStockBundle:PurchaseOrderItem')
            ->findByZedProductAndCostAndPurchaseOrderAndStatus(
                $oldZedProduct,
                $purchaseOrderItem->getCost(),
                $purchaseOrder,
                $initialStatus
            );

        $oldQuantity = count($purchaseOrderItemList);
        $this->assertTrue($oldQuantity >= 1);
        $changedQuantity = $oldQuantity + 10;

        $form[self::FORM_COST]        = $changedCost;
        $form[self::FORM_ZED_PRODUCT] = $changedZedProductId;
        $form[self::FORM_QUANTITY]    = $changedQuantity;

        $crawler = $client->submit($form);

        self::$entityManager->refresh($purchaseOrderItem);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Updated') . '")')->count()
        );

        $this->assertEquals($changedCost, $purchaseOrderItem->getCost());
        $this->assertNotEquals($changedZedProductId, $purchaseOrderItem->getZedProduct()->getId());
        $this->assertEquals($oldZedProduct->getId(), $purchaseOrderItem->getZedProduct()->getId());

        $purchaseOrderItemList = self::$entityManager->getRepository('NatueStockBundle:PurchaseOrderItem')
            ->findByZedProductAndCostAndPurchaseOrderAndStatus(
                $oldZedProduct,
                $changedCost,
                $purchaseOrder,
                $initialStatus
            );
        $realQuantity          = count($purchaseOrderItemList);
        $this->assertEquals($changedQuantity, $realQuantity);

        // now we test that we can remove 5 items with the update method.
        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_purchase_order_item_update',
                [
                    'purchaseOrderId' => $purchaseOrder->getId(),
                    'cost'            => $changedCost,
                    'sku'             => $oldZedProduct->getSku()
                ]
            )
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(4, $crawler->filterXPath('//input')->count());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Update Purchase Order Item') . '")')->count()
        );

        $changedQuantity -= 3; // 3 is an arbitrary number

        $form                         = $crawler->filterXpath('//button[@type="submit"]')->form();
        $form[self::FORM_COST]        = $changedCost;
        $form[self::FORM_ZED_PRODUCT] = $changedZedProductId;
        $form[self::FORM_QUANTITY]    = $changedQuantity;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Updated') . '")')->count()
        );

        $purchaseOrderItemList = self::$entityManager->getRepository('NatueStockBundle:PurchaseOrderItem')
            ->findByZedProductAndCostAndPurchaseOrderAndStatus(
                $oldZedProduct,
                $changedCost,
                $purchaseOrder,
                $initialStatus
            );
        $realQuantity          = count($purchaseOrderItemList);
        $this->assertEquals($changedQuantity, $realQuantity);
    }

    /**
     * Test the method updateAction from PurchaseOrderItem
     *
     * @return void
     */
    public function testEmptyUpdateAction()
    {
        $client = self::$client;

        $this->setExpectedException('Symfony\Component\Routing\Exception\MissingMandatoryParametersException');

        $client->request('GET', self::$router->generate('stock_purchase_order_item_update'));
    }

    /**
     * Test the method updateAction with invalid arguments from PurchaseOrderItem
     *
     * @return void
     */
    public function testInvalidUpdatedAction()
    {
        $client = self::$client;

        $purchaseOrderItem = $this->purchaseOrderItemFactory();
        self::$entityManager->persist($purchaseOrderItem);
        self::$entityManager->flush();

        $wrongPurchaseOrderId = $this->generateWrongPurchaseOrderId();

        $wrongSku = uniqid();

        $wrongCost = -1;

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_purchase_order_item_update',
                [
                    'purchaseOrderId' => $wrongPurchaseOrderId,
                    'cost'            => $purchaseOrderItem->getCost(),
                    'sku'             => $purchaseOrderItem->getZedProduct()->getSku()
                ]
            )
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans(
                    'Purchase Order with id:'
                    . $wrongPurchaseOrderId . ' not found'
                ) . '")'
            )->count()
        );

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_purchase_order_item_update',
                [
                    'purchaseOrderId' => $purchaseOrderItem->getPurchaseOrder()->getId(),
                    'cost'            => $purchaseOrderItem->getCost(),
                    'sku'             => $wrongSku
                ]
            )
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans(
                    'Zed Product with sku:' . $wrongSku .
                    ' not found'
                ) . '")'
            )->count()
        );

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_purchase_order_item_update',
                [
                    'purchaseOrderId' => $purchaseOrderItem->getPurchaseOrder()->getId(),
                    'cost'            => $wrongCost,
                    'sku'             => $purchaseOrderItem->getZedProduct()->getSku()
                ]
            )
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans('Purchase Order Item not found')
                . '")'
            )->count()
        );
    }

    /**
     * Test the method deleteAction from PurchaseOrderItem
     *
     * @return void
     */
    public function testDeleteAction()
    {
        $client            = self::$client;
        $purchaseOrderItem = $this->purchaseOrderItemFactory();

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_purchase_order_item_delete',
                [
                    'purchaseOrderId' => $purchaseOrderItem->getPurchaseOrder()->getId(),
                    'cost'            => $purchaseOrderItem->getCost(),
                    'sku'             => $purchaseOrderItem->getZedProduct()->getSku(),
                    'status'          => $purchaseOrderItem->getStatus(),
                ]
            )
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Deleted') . '")')->count()
        );

        self::$entityManager->refresh($purchaseOrderItem);

        $this->assertEquals(EnumPurchaseOrderItemStatusType::STATUS_DELETED, $purchaseOrderItem->getStatus());
    }

    /**
     * Test the method deleteAction from PurchaseOrderItem
     *
     * @return void
     */
    public function testEmptyDeleteAction()
    {
        $client = self::$client;

        $this->setExpectedException('Symfony\Component\Routing\Exception\MissingMandatoryParametersException');

        $client->request('GET', self::$router->generate('stock_purchase_order_item_delete'));
    }

    /**
     * Test the method deleteAction with invalid argument from PurchaseOrderItem
     *
     * @return void
     */
    public function testInvalidDeleteAction()
    {
        $client = self::$client;

        $purchaseOrderItem = $this->purchaseOrderItemFactory();
        self::$entityManager->persist($purchaseOrderItem);
        self::$entityManager->flush();

        $wrongPurchaseOrderId = $this->generateWrongPurchaseOrderId();

        $wrongSku = uniqid();

        $wrongCost = $purchaseOrderItem->getCost() - 1;

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_purchase_order_item_delete',
                [
                    'purchaseOrderId' => $wrongPurchaseOrderId,
                    'cost'            => $purchaseOrderItem->getCost(),
                    'sku'             => $purchaseOrderItem->getZedProduct()->getSku(),
                    'status'          => $purchaseOrderItem->getStatus()
                ]
            )
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans(
                    'Purchase Order with id:'
                    . $wrongPurchaseOrderId . ' not found'
                ) . '")'
            )->count()
        );

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_purchase_order_item_delete',
                [
                    'purchaseOrderId' => $purchaseOrderItem->getPurchaseOrder()->getId(),
                    'cost'            => $purchaseOrderItem->getCost(),
                    'sku'             => $wrongSku,
                    'status'          => $purchaseOrderItem->getStatus()
                ]
            )
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans(
                    'Zed Product with sku:' . $wrongSku .
                    ' not found'
                ) . '")'
            )->count()
        );

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_purchase_order_item_delete',
                [
                    'purchaseOrderId' => $purchaseOrderItem->getPurchaseOrder()->getId(),
                    'cost'            => $wrongCost,
                    'sku'             => $purchaseOrderItem->getZedProduct()->getSku(),
                    'status'          => $purchaseOrderItem->getStatus(),
                ]
            )
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans('No Purchase Order Items were found')
                . '")'
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
