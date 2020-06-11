<?php

namespace Natue\Bundle\StockBundle\Tests\Controller;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\StockBundle\DataFixtures\Constants;

class InventoryTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testIndex()
    {
        $crawler = self::$client->request('GET', self::$router->generate('stock_inventory_list'));

        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Inventorisation') . '")')->count()
        );

        $this->assertEquals(
            1,
            $crawler->filter("[href='" . self::$router->generate('stock_inventory_create') . "']")->count()
        );

        $this->assertEquals(
            1,
            $crawler->filter(".pedroteixeira-grid-wrapper")->count()
        );
    }

    /**
     * @return void
     */
    public function testInventoryCreateActionNegative()
    {
        $testInvalidPosition = 'test_invalid_position';

        $url = self::$router->generate('stock_inventory_create');
        $crawler = self::$client->request('GET', $url);

        $submitButtonSelector = "form[action='$url'] .btn-primary";
        $this->assertEquals(1, $crawler->filter("[name='natue_inventory[stock_position_name]']")->count());
        $this->assertEquals(1, $crawler->filter($submitButtonSelector)->count());

        $form = $crawler->filter($submitButtonSelector)->form();

        $crawler = self::$client->submit(
            $form,
            [
                'natue_inventory[stock_position_name]' => $testInvalidPosition
            ]
        );

        $this->assertTrue(self::$client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter("html:contains(Stock Position $testInvalidPosition not found)")->count()
        );
    }

    /**
     * @return void
     */
    public function testInventoryCreateActionNegativeLocked()
    {
        $url = self::$router->generate('stock_inventory_create');
        $crawler = self::$client->request('GET', $url);

        $submitButtonSelector = "form[action='$url'] .btn-primary";
        $this->assertEquals(1, $crawler->filter("[name='natue_inventory[stock_position_name]']")->count());
        $this->assertEquals(1, $crawler->filter($submitButtonSelector)->count());

        $form = $crawler->filter($submitButtonSelector)->form();

        $crawler = self::$client->submit(
            $form,
            [
                'natue_inventory[stock_position_name]' => Constants::POSITION_INVENTORIZED
            ]
        );

        $this->assertTrue(self::$client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter("html:contains(Inventory for this position already started)")->count());
    }

    /**
     * @return void
     */
    public function testLinkNewInventory()
    {
        $crawler = self::$client->request('GET', self::$router->generate('stock_inventory_list'));

        $link = $crawler->selectLink(self::$translator->trans('New Inventory'))->link();
        self::$client->click($link);

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(
            self::$router->generate('stock_inventory_create'),
            self::$client->getRequest()->getRequestUri()
        );
    }

    /**
     * @return void
     */
    public function testSimpleInventory()
    {
        $url = self::$router->generate('stock_inventory_create');
        $crawler = self::$client->request('GET', $url);

        $submitButtonSelector = "form[action='$url'] .btn-primary";
        $this->assertEquals(1, $crawler->filter("[name='natue_inventory[stock_position_name]']")->count());
        $this->assertEquals(1, $crawler->filter($submitButtonSelector)->count());

        $crawler = self::$client->submit(
            $crawler->filter($submitButtonSelector)->form(),
            [
                'natue_inventory[stock_position_name]' => Constants::POSITION_ITEM_SINGLE
            ]
        );

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("html:contains(" . self::$translator->trans('Created') . ")")->count());

        $crawler = self::$client->submit(
            $crawler->filter("#qa-addItems")->form(),
            [
                'natue_inventory_items[barcode]'  => 12345,
                'natue_inventory_items[quantity]' => 1
            ]
        );

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("html:contains(Items added)")->count());

        $link = $crawler->selectLink(self::$translator->trans('Do control inventory'))->link();
        $crawler = self::$client->click($link);

        $this->assertEquals(
            1,
            $crawler->filter(
                "html:contains('Inventory succeeded! Position: " . Constants::POSITION_ITEM_SINGLE . "')"
            )->count()
        );
    }

    /**
     * @return void
     */
    public function testInventoryItemsWithLessItemQuantityThanExpected()
    {
        $url = self::$router->generate('stock_inventory_create');
        $crawler = self::$client->request('GET', $url);

        $submitButtonSelector = "form[action='$url'] .btn-primary";
        $this->assertEquals(1, $crawler->filter("[name='natue_inventory[stock_position_name]']")->count());
        $this->assertEquals(1, $crawler->filter($submitButtonSelector)->count());

        $crawler = self::$client->submit(
            $crawler->filter($submitButtonSelector)->form(),
            [
                'natue_inventory[stock_position_name]' => Constants::POSITION_ITEM_SINGLE_QUANTITY_2
            ]
        );

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("html:contains(" . self::$translator->trans('Created') . ")")->count());

        $crawler = self::$client->submit(
            $crawler->filter("#qa-addItems")->form(),
            [
                'natue_inventory_items[barcode]'  => 'item1Quantity2',
                'natue_inventory_items[quantity]' => 1
            ]
        );

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("html:contains(Items added)")->count());

        $link = $crawler->selectLink(self::$translator->trans('Do control inventory'))->link();
        $crawler = self::$client->click($link);

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(
            1,
            $crawler->filter(
                "html:contains('Please do inventory again on position: " .
                Constants::POSITION_ITEM_SINGLE_QUANTITY_2 . "')"
            )->count()
        );

        $crawler = self::$client->submit(
            $crawler->filter("#qa-addItems")->form(),
            [
                'natue_inventory_items[barcode]'  => 'item1Quantity2',
                'natue_inventory_items[quantity]' => 1
            ]
        );

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("html:contains(Items added)")->count());

        $link = $crawler->selectLink(self::$translator->trans('Do control inventory'))->link();
        $crawler = self::$client->click($link);

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(
            1,
            $crawler->filter(
                "html:contains('Inventory succeeded! Position: " .
                Constants::POSITION_ITEM_SINGLE_QUANTITY_2 . "')"
            )->count()
        );
    }

    /**
     * @return void
     */
    public function testInventoryItemsWithMoreItemQuantityThanExpected()
    {
        $url = self::$router->generate('stock_inventory_create');
        $crawler = self::$client->request('GET', $url);

        $submitButtonSelector = "form[action='$url'] .btn-primary";
        $this->assertEquals(1, $crawler->filter("[name='natue_inventory[stock_position_name]']")->count());
        $this->assertEquals(1, $crawler->filter($submitButtonSelector)->count());

        $crawler = self::$client->submit(
            $crawler->filter($submitButtonSelector)->form(),
            [
                'natue_inventory[stock_position_name]' => Constants::POSITION_ITEM_SINGLE_QUANTITY_2
            ]
        );

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("html:contains(" . self::$translator->trans('Created') . ")")->count());

        $crawler = self::$client->submit(
            $crawler->filter("#qa-addItems")->form(),
            [
                'natue_inventory_items[barcode]'  => 'item1Quantity2',
                'natue_inventory_items[quantity]' => 4
            ]
        );

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(
            1,
            $crawler->filter(
                "html:contains('" .
                'You entered more items then in the system. Total items found: 2' .
                "')"
            )->count()
        );

        $crawler = self::$client->submit(
            $crawler->filter("#qa-addItems")->form(),
            [
                'natue_inventory_items[barcode]'  => 'item1Quantity2',
                'natue_inventory_items[quantity]' => 2
            ]
        );

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("html:contains(Items added)")->count());

        $link = $crawler->selectLink(self::$translator->trans('Do control inventory'))->link();
        $crawler = self::$client->click($link);

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(
            1,
            $crawler->filter(
                "html:contains('Inventory succeeded! Position: " .
                Constants::POSITION_ITEM_SINGLE_QUANTITY_2 . "')"
            )->count()
        );
    }

    /**
     * @return void
     */
    public function testInventoryStartForTheSamePositionTwice()
    {
        $url = self::$router->generate('stock_inventory_create');
        $crawler = self::$client->request('GET', $url);

        $submitButtonSelector = "form[action='$url'] .btn-primary";
        $this->assertEquals(1, $crawler->filter("[name='natue_inventory[stock_position_name]']")->count());
        $this->assertEquals(1, $crawler->filter($submitButtonSelector)->count());

        $crawler = self::$client->submit(
            $crawler->filter($submitButtonSelector)->form(),
            [
                'natue_inventory[stock_position_name]' => Constants::POSITION_ITEM_SINGLE
            ]
        );

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("html:contains(" . self::$translator->trans('Created') . ")")->count());

        $inventoryUrl = self::$client->getRequest()->getRequestUri();

        // trying to create inventory second time with the same user
        $crawler = self::$client->request('GET', $url);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $crawler = self::$client->submit(
            $crawler->filter($submitButtonSelector)->form(),
            [
                'natue_inventory[stock_position_name]' => Constants::POSITION_ITEM_SINGLE
            ]
        );

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("html:contains(" . self::$translator->trans('Created') . ")")->count());

        // checking if we got the same inventory
        $this->assertEquals($inventoryUrl, self::$client->getRequest()->getRequestUri());

        $crawler = self::$client->submit(
            $crawler->filter("#qa-addItems")->form(),
            [
                'natue_inventory_items[barcode]'  => 12345,
                'natue_inventory_items[quantity]' => 1
            ]
        );

        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter("html:contains(Items added)")->count());

        $link = $crawler->selectLink(self::$translator->trans('Do control inventory'))->link();
        $crawler = self::$client->click($link);

        $this->assertEquals(
            1,
            $crawler->filter(
                "html:contains('Inventory succeeded! Position: " . Constants::POSITION_ITEM_SINGLE . "')"
            )->count()
        );
    }
}
