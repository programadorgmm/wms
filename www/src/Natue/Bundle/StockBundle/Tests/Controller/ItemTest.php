<?php

namespace Natue\Bundle\StockBundle\Tests\Controller;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;

/**
 * Item test
 */
class ItemTest extends WebTestCase
{
    /**
     * Test the method listAction from ItemController
     *
     * @return void
     */
    public function testListAction()
    {
        $client = self::$client;

        $stockPosition = $this->stockPositionFactory();
        $stockItem     = $this->stockItemFactory(['status' => EnumStockItemStatusType::STATUS_READY]);
        $stockItem->setStockPosition($stockPosition);

        self::$entityManager->persist($stockItem);
        self::$entityManager->flush($stockItem);

        $crawler = $client->request('GET', self::$router->generate('stock_item_list'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('table.pedroteixeira-grid-table')->count());

        $client->request(
            'GET',
            self::$router->generate('stock_item_list'),
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
     * Test the method listAction with invalid arguments from ItemController
     *
     * @return void
     */
    public function testInvalidListAction()
    {
        $client = self::$client;

        $stockPosition = $this->stockPositionFactory();
        $stockItem     = $this->stockItemFactory();
        $stockItem->setStockPosition($stockPosition);

        $wrongBarcode  = uniqid();
        $wrongPosition = uniqid();
        self::$entityManager->persist($stockItem);
        self::$entityManager->flush($stockItem);

        $crawler = $client->request('GET', self::$router->generate('stock_item_list'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('table.pedroteixeira-grid-table')->count());

        $client->request(
            'GET',
            self::$router->generate('stock_item_list'),
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
     * Test the method updateAction from ItemController
     *
     * @return void
     */
    public function testUpdateAction()
    {
        $client = self::$client;
        $entity = $this->stockItemFactory(
            [
                'StockPosition' => $this->stockPositionFactory()
            ]
        );

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_item_update',
                [
                    'sku'            => $entity->getZedProduct()->getSku(),
                    'positionId'     => $entity->getStockPosition()->getId(),
                    'status'         => $entity->getStatus(),
                    'barcode'        => $entity->getBarcode(),
                    'dateExpiration' => $entity->getDateExpiration()->format('Y-m-d'),
                ]
            )
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Update Stock Item') . '")')->count()
        );

        $changedName = 'Changed #' . uniqid();

        $form                                    = $crawler->filterXpath('//button[@type="submit"]')->form();
        $form['natue_stockbundle_item[barcode]'] = $changedName;

        $crawler = $client->submit($form);

        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Updated') . '")')->count()
        );

        self::$entityManager->refresh($entity);

        $this->assertEquals($changedName, $entity->getBarcode());
    }

    /**
     * Test the method updateAction from ItemController
     *
     * @return void
     */
    public function testEmptyUpdateAction()
    {
        $client = self::$client;

        $this->setExpectedException('Symfony\Component\Routing\Exception\MissingMandatoryParametersException');

        $client->request('GET', self::$router->generate('stock_item_update'));
    }

    /**
     * Test the method updateAction with invalid arguments from ItemController
     *
     * @return void
     */
    public function testInvalidUpdateAction()
    {
        $client = self::$client;

        $entity = $this->stockItemFactory(
            [
                'StockPosition' => $this->stockPositionFactory()
            ]
        );
        self::$entityManager->persist($entity);
        self::$entityManager->flush();

        $repository      = self::$entityManager->getRepository('NatueStockBundle:StockPosition');
        $queryBuilder    = $repository->createQueryBuilder('stockPosition')
            ->select('MAX(stockPosition.id)');
        $wrongPositionId = $queryBuilder->getQuery()->getSingleScalarResult() + 1;

        $wrongStatus = 'WRONG! Status';

        $wrongSku     = uniqid();
        $wrongBarcode = uniqid();

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_item_update',
                [
                    'sku'            => $entity->getZedProduct()->getSku(),
                    'positionId'     => $entity->getStockPosition()->getId(),
                    'status'         => $entity->getStatus(),
                    'barcode'        => $entity->getBarcode(),
                    'dateExpiration' => $entity->getDateExpiration()->format('Y-m-d'),
                ]
            )
        );

        $form = $crawler->filterXpath('//button[@type="submit"]')->form();

        $crawler = $client->submit($form);

        $form = $crawler->filterXpath('//button[@type="submit"]')->form();

        $crawler = $client->submit($form);

        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Error on form submission') . '")')->count()
        );

        $form = $crawler->filterXpath('//button[@type="submit"]')->form();


        $crawler = $client->submit($form);

        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Error on form submission') . '")')->count()
        );

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_item_update',
                [
                    'sku'            => $entity->getZedProduct()->getSku(),
                    'positionId'     => $wrongPositionId,
                    'status'         => $entity->getStatus(),
                    'barcode'        => $entity->getBarcode(),
                    'dateExpiration' => $entity->getDateExpiration()->format('Y-m-d'),
                ]
            )
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans(' Position not found ') . '")')->count()
        );

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_item_update',
                [
                    'sku'            => $entity->getZedProduct()->getSku(),
                    'positionId'     => $entity->getStockPosition()->getId(),
                    'status'         => $wrongStatus,
                    'barcode'        => $entity->getBarcode(),
                    'dateExpiration' => $entity->getDateExpiration()->format('Y-m-d'),
                ]
            )
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Stock item not found') . '")')->count()
        );

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_item_update',
                [
                    'sku'            => $wrongSku,
                    'positionId'     => $entity->getStockPosition()->getId(),
                    'status'         => $entity->getStatus(),
                    'barcode'        => $entity->getBarcode(),
                    'dateExpiration' => $entity->getDateExpiration()->format('Y-m-d'),
                ]
            )
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Product not found') . '")')->count()
        );

        $crawler = $client->request(
            'GET',
            self::$router->generate(
                'stock_item_update',
                [
                    'sku'            => $entity->getZedProduct()->getSku(),
                    'positionId'     => $entity->getStockPosition()->getId(),
                    'status'         => $entity->getStatus(),
                    'barcode'        => $wrongBarcode,
                    'dateExpiration' => $entity->getDateExpiration()->format('Y-m-d'),
                ]
            )
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Stock item not found') . '")')->count()
        );
    }

    /**
     * Test the method moveFromPositionAction from ItemController
     *
     * @return void
     */
    public function testMoveFromPositionAction()
    {
        $client = self::$client;

        $stockItem1 = $this->stockItemFactory();
        $stockItem2 = $this->stockItemFactory();

        $positionFrom = $this->stockPositionFactory();
        $positionTo   = $this->stockPositionFactory();

        $stockItem1->setStockPosition($positionFrom);
        $stockItem2->setStockPosition($positionFrom);

        self::$entityManager->persist($stockItem1);
        self::$entityManager->persist($stockItem2);
        self::$entityManager->flush();

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_item_move_from_position')
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Move items by position') . '")')->count()
        );

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position_move_from_position[old_stock_position_id]'] = $positionFrom->getId();
        $form['natue_stockbundle_position_move_from_position[new_stock_position_id]'] = $positionTo->getId();

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans('Items were successfully moved') . '")'
            )->count()
        );

        self::$entityManager->refresh($stockItem1);
        self::$entityManager->refresh($stockItem2);

        $this->assertEquals($stockItem1->getStockPosition(), $positionTo);
        $this->assertEquals($stockItem2->getStockPosition(), $positionTo);
    }

    /**
     * Test the method moveFromPositionAction with invalid arguments from ItemController
     *
     * @return void
     */
    public function testInvalidMoveFromPositionAction()
    {
        $client = self::$client;

        $stockItem1 = $this->stockItemFactory();
        $stockItem2 = $this->stockItemFactory();

        $positionFrom = $this->stockPositionFactory();
        $positionTo   = $this->stockPositionFactory();

        $stockItem1->setStockPosition($positionFrom);
        $stockItem2->setStockPosition($positionFrom);

        self::$entityManager->persist($stockItem1);
        self::$entityManager->persist($stockItem2);
        self::$entityManager->flush();

        $repository    = self::$entityManager->getRepository('NatueStockBundle:StockPosition');
        $queryBuilder  = $repository->createQueryBuilder('stockPosition')
            ->select('MAX(stockPosition.id)');
        $wrongPosition = $queryBuilder->getQuery()->getSingleScalarResult() + 1;

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_item_move_from_position')
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Move items by position') . '")')->count()
        );

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position_move_from_position[old_stock_position_id]'] = $wrongPosition;
        $form['natue_stockbundle_position_move_from_position[new_stock_position_id]'] = $positionTo->getId();

        $client->submit($form);

        $this->assertFalse($client->getResponse()->isSuccessful());

        self::$entityManager->refresh($stockItem1);
        self::$entityManager->refresh($stockItem2);

        $this->assertEquals($stockItem1->getStockPosition(), $positionFrom);
        $this->assertEquals($stockItem2->getStockPosition(), $positionFrom);

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_item_move_from_position')
        );

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position_move_from_position[old_stock_position_id]'] = $positionFrom->getId();
        $form['natue_stockbundle_position_move_from_position[new_stock_position_id]'] = $wrongPosition;

        $client->submit($form);

        $this->assertFalse($client->getResponse()->isSuccessful());

        self::$entityManager->refresh($stockItem1);
        self::$entityManager->refresh($stockItem2);

        $this->assertEquals($stockItem1->getStockPosition(), $positionFrom);
        $this->assertEquals($stockItem2->getStockPosition(), $positionFrom);

        $client->request(
            'GET',
            self::$router->generate('stock_item_move_from_position')
        );

        $form['natue_stockbundle_position_move_from_position[old_stock_position_id]'] = $positionFrom->getId();
        $form['natue_stockbundle_position_move_from_position[new_stock_position_id]'] = $positionFrom->getId();

        $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());

        self::$entityManager->refresh($stockItem1);
        self::$entityManager->refresh($stockItem2);

        $this->assertEquals($stockItem1->getStockPosition(), $positionFrom);
        $this->assertEquals($stockItem2->getStockPosition(), $positionFrom);

        self::$entityManager->remove($stockItem1);
        self::$entityManager->remove($stockItem2);

        self::$entityManager->flush();

        $client->request(
            'GET',
            self::$router->generate('stock_item_move_from_position')
        );

        $form['natue_stockbundle_position_move_from_position[old_stock_position_id]'] = $positionFrom->getId();
        $form['natue_stockbundle_position_move_from_position[new_stock_position_id]'] = $positionTo->getId();

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans('No stock item found') . '")'
            )->count()
        );
    }

    /**
     * Test the method moveAction from ItemController
     *
     * @return void
     */
    public function testMoveAction()
    {
        $client = self::$client;

        $stockItem1 = $this->stockItemFactory(['status' => EnumStockItemStatusType::STATUS_READY]);
        $stockItem2 = $this->stockItemFactory(['status' => EnumStockItemStatusType::STATUS_READY]);

        $positionFrom = $this->stockPositionFactory();
        $positionTo   = $this->stockPositionFactory();

        $stockItem1->setStockPosition($positionFrom);
        $stockItem2->setStockPosition($positionFrom);

        $barcodeStockItem1 = $stockItem1->getBarcode();
        $stockItem2->setBarcode($barcodeStockItem1);

        self::$entityManager->persist($stockItem1);
        self::$entityManager->persist($stockItem2);
        self::$entityManager->flush();

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_item_move')
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Move items') . '")')->count()
        );

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position_move[old_stock_position_id]'] = $positionFrom->getId();
        $form['natue_stockbundle_position_move[new_stock_position_id]'] = $positionTo->getId();
        $form['natue_stockbundle_position_move[quantity]']              = 2;
        $form['natue_stockbundle_position_move[barcode]']               = $stockItem1->getBarcode();

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans('Items were successfully moved') . '")'
            )->count()
        );

        self::$entityManager->refresh($stockItem1);
        self::$entityManager->refresh($stockItem2);

        $this->assertEquals($stockItem1->getStockPosition(), $positionTo);
        $this->assertEquals($stockItem2->getStockPosition(), $positionTo);
    }

    /**
     * Test the method moveAction with invalid arguments from ItemController
     *
     * @return void
     */
    public function testInvalidMoveAction()
    {
        $client = self::$client;

        $stockItem1 = $this->stockItemFactory(['status' => EnumStockItemStatusType::STATUS_READY]);
        $stockItem2 = $this->stockItemFactory(['status' => EnumStockItemStatusType::STATUS_READY]);

        $positionFrom = $this->stockPositionFactory();
        $positionTo   = $this->stockPositionFactory();

        $stockItem1->setStockPosition($positionFrom);
        $stockItem2->setStockPosition($positionFrom);
        $stockItem2->setBarcode($stockItem1->getBarcode());

        self::$entityManager->persist($stockItem1);
        self::$entityManager->persist($stockItem2);
        self::$entityManager->flush();

        $validQuantity         = 2;
        $invalidStringQuantity = uniqid();
        $invalidTooBigQuantity = 10; // Quantity > # of items at this position
        $validBarcode          = $stockItem1->getBarcode();
        $invalidBarcode        = uniqid();

        $repository    = self::$entityManager->getRepository('NatueStockBundle:StockPosition');
        $queryBuilder  = $repository->createQueryBuilder('stockPosition')
            ->select('MAX(stockPosition.id)');
        $wrongPosition = $queryBuilder->getQuery()->getSingleScalarResult() + 1;

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_item_move')
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Move items') . '")')->count()
        );

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position_move[old_stock_position_id]'] = $wrongPosition;
        $form['natue_stockbundle_position_move[new_stock_position_id]'] = $positionTo->getId();
        $form['natue_stockbundle_position_move[quantity]']              = $validQuantity;
        $form['natue_stockbundle_position_move[barcode]']               = $validBarcode;

        $client->submit($form);

        $this->assertFalse($client->getResponse()->isSuccessful());

        self::$entityManager->refresh($stockItem1);
        self::$entityManager->refresh($stockItem2);

        $this->assertEquals($stockItem1->getStockPosition(), $positionFrom);
        $this->assertEquals($stockItem2->getStockPosition(), $positionFrom);

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_item_move')
        );

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position_move[old_stock_position_id]'] = $positionFrom->getId();
        $form['natue_stockbundle_position_move[new_stock_position_id]'] = $wrongPosition;
        $form['natue_stockbundle_position_move[quantity]']              = $validQuantity;
        $form['natue_stockbundle_position_move[barcode]']               = $validBarcode;

        $client->submit($form);

        $this->assertFalse($client->getResponse()->isSuccessful());

        self::$entityManager->refresh($stockItem1);
        self::$entityManager->refresh($stockItem2);

        $this->assertEquals($stockItem1->getStockPosition(), $positionFrom);
        $this->assertEquals($stockItem2->getStockPosition(), $positionFrom);

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_item_move')
        );

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position_move[old_stock_position_id]'] = $positionFrom->getId();
        $form['natue_stockbundle_position_move[new_stock_position_id]'] = $positionTo->getId();
        $form['natue_stockbundle_position_move[quantity]']              = $invalidStringQuantity;
        $form['natue_stockbundle_position_move[barcode]']               = $validBarcode;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());

        self::$entityManager->refresh($stockItem1);
        self::$entityManager->refresh($stockItem2);

        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans('Error on form submission') . '")'
            )->count()
        );
        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans('This value is not valid.') . '")'
            )->count()
        );
        $this->assertEquals($stockItem1->getStockPosition(), $positionFrom);
        $this->assertEquals($stockItem2->getStockPosition(), $positionFrom);


        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_item_move')
        );

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position_move[old_stock_position_id]'] = $positionFrom->getId();
        $form['natue_stockbundle_position_move[new_stock_position_id]'] = $positionTo->getId();
        $form['natue_stockbundle_position_move[quantity]']              = $invalidTooBigQuantity;
        $form['natue_stockbundle_position_move[barcode]']               = $validBarcode;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());

        self::$entityManager->refresh($stockItem1);
        self::$entityManager->refresh($stockItem2);

        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans('Quantity is greater than the number of stock item') . '")'
            )->count()
        );
        $this->assertEquals($stockItem1->getStockPosition(), $positionFrom);
        $this->assertEquals($stockItem2->getStockPosition(), $positionFrom);

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_item_move')
        );

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position_move[old_stock_position_id]'] = $positionFrom->getId();
        $form['natue_stockbundle_position_move[new_stock_position_id]'] = $positionTo->getId();
        $form['natue_stockbundle_position_move[quantity]']              = $validQuantity;
        $form['natue_stockbundle_position_move[barcode]']               = $invalidBarcode;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());

        self::$entityManager->refresh($stockItem1);
        self::$entityManager->refresh($stockItem2);

        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans('No stock item found from barcode:') . '")'
            )->count()
        );
        $this->assertEquals($stockItem1->getStockPosition(), $positionFrom);
        $this->assertEquals($stockItem2->getStockPosition(), $positionFrom);

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_item_move')
        );

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position_move[old_stock_position_id]'] = $positionFrom->getId();
        $form['natue_stockbundle_position_move[new_stock_position_id]'] = $positionFrom->getId();
        $form['natue_stockbundle_position_move[quantity]']              = $validQuantity;
        $form['natue_stockbundle_position_move[barcode]']               = $validBarcode;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());

        self::$entityManager->refresh($stockItem1);
        self::$entityManager->refresh($stockItem2);

        $this->assertEquals(
            1,
            $crawler->filter(
                'html:contains("' . self::$translator->trans('Position "from" and Position "to" are the same') . '")'
            )->count()
        );
        $this->assertEquals($stockItem1->getStockPosition(), $positionFrom);
        $this->assertEquals($stockItem2->getStockPosition(), $positionFrom);
    }
}
