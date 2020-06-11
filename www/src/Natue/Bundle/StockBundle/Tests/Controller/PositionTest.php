<?php

namespace Natue\Bundle\StockBundle\Tests\Controller;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * Position test
 */
class PositionTest extends WebTestCase
{
    /**
     * Test the method listAction from PositionController
     *
     * @return void
     */
    public function testListAction()
    {
        $client = self::$client;

        $crawler = $client->request('GET', self::$router->generate('stock_position_list'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('table.pedroteixeira-grid-table')->count());

        $client->request(
            'GET',
            self::$router->generate('stock_position_list'),
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
     * Test the method createAction from PositionController
     *
     * @return void
     */
    public function testCreateAction()
    {
        $client = self::$client;

        $crawler = $client->request('GET', self::$router->generate('stock_position_create'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(3, $crawler->filterXPath('//input')->count());
        $this->assertEquals(2, $crawler->filterXPath('//select')->count());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position[name]']     = 'Name #' . uniqid();
        $form['natue_stockbundle_position[sort]']     = rand(1, 1000);
        $form['natue_stockbundle_position[pickable]'] = true;
        $form['natue_stockbundle_position[enabled]']  = true;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Created') . '")')->count()
        );
    }


    /**
     * Test the method createAction with invalid arguments from PositionController
     *
     * @return void
     */
    public function testInvalidCreateAction()
    {
        $client = self::$client;

        // An invalid name is a name that already exist (name is unique)
        $stockPosition = $this->stockPositionFactory();
        self::$entityManager->persist($stockPosition);
        self::$entityManager->flush();
        $invalidPositionName = $stockPosition->getName();

        $crawler = $client->request('GET', self::$router->generate('stock_position_create'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(3, $crawler->filterXPath('//input')->count());
        $this->assertEquals(2, $crawler->filterXPath('//select')->count());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position[name]']     = $invalidPositionName;
        $form['natue_stockbundle_position[pickable]'] = true;
        $form['natue_stockbundle_position[enabled]']  = true;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Position already exist') . '")')->count()
        );
    }

    /**
     * Test the method updateAction from PositionController
     *
     * @return void
     */
    public function testUpdateAction()
    {
        $client = self::$client;
        $entity = $this->stockPositionFactory();

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_position_update', ['id' => $entity->getId()])
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Update Position') . '")')->count()
        );

        $changedName = 'Changed #' . uniqid();

        $form                                     = $crawler->filterXpath('//button[@type="submit"]')->form();
        $form['natue_stockbundle_position[name]'] = $changedName;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Updated') . '")')->count()
        );

        self::$entityManager->refresh($entity);

        $this->assertEquals($changedName, $entity->getName());
    }


    /**
     * Test the method updateAction from PositionController
     *
     * @return void
     */
    public function testEmptyUpdateAction()
    {
        $client = self::$client;

        $this->setExpectedException('Symfony\Component\Routing\Exception\MissingMandatoryParametersException');

        $client->request('GET', self::$router->generate('stock_position_update'));
    }

    /**
     * Test the method createAction with invalid arguments from PositionController
     *
     * @return void
     */
    public function testInvalidUpdateAction()
    {
        $client = self::$client;

        $stockPosition1 = $this->stockPositionFactory();
        $stockPosition2 = $this->stockPositionFactory();
        self::$entityManager->persist($stockPosition1);
        self::$entityManager->persist($stockPosition2);
        self::$entityManager->flush();
        // An invalid name is a name that already exist (name is unique)
        $invalidPositionName = $stockPosition2->getName();

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_position_update', ['id' => $stockPosition1->getId()])
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(3, $crawler->filterXPath('//input')->count());
        $this->assertEquals(2, $crawler->filterXPath('//select')->count());

        $form = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();

        $form['natue_stockbundle_position[name]']     = $invalidPositionName;
        $form['natue_stockbundle_position[pickable]'] = true;
        $form['natue_stockbundle_position[enabled]']  = true;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Position already exist') . '")')->count()
        );
    }

    /**
     * Test the method deleteAction from PositionController
     *
     * @return void
     */
    public function testDeleteAction()
    {
        $client = self::$client;
        $entity = $this->stockPositionFactory();

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_position_delete', ['id' => $entity->getId()])
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Deleted') . '")')->count()
        );
    }

    /**
     * Test the method deleteAction from PositionController
     *
     * @return void
     */
    public function testEmptyDeleteAction()
    {
        $client = self::$client;

        $this->setExpectedException('Symfony\Component\Routing\Exception\MissingMandatoryParametersException');

        $client->request('GET', self::$router->generate('stock_position_delete'));
    }

    /**
     * Test the method deleteAction with invalid arguments from PositionController
     *
     * @return void
     */
    public function testInvalidDeleteAction()
    {
        $client = self::$client;

        $repository      = self::$entityManager->getRepository('NatueStockBundle:StockPosition');
        $queryBuilder    = $repository->createQueryBuilder('stockPosition')
            ->select('MAX(stockPosition.id)');
        $wrongPositionId = $queryBuilder->getQuery()->getSingleScalarResult() + 1;

        $crawler = $client->request(
            'GET',
            self::$router->generate('stock_position_update', ['id' => $wrongPositionId])
        );

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Not found') . '")')->count()
        );
    }
}
