<?php

namespace Natue\Bundle\UserBundle\Tests\Controller;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * Group test
 */
class GroupTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testListAction()
    {
        $client = self::$client;

        $crawler = $client->request('GET', self::$router->generate('user_group_list'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('table.pedroteixeira-grid-table')->count());

        $client->request(
            'GET',
            self::$router->generate('user_group_list'),
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
     * @return void
     */
    public function testCreateAction()
    {
        $client = self::$client;

        $crawler = $client->request('GET', self::$router->generate('user_group_create'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(2, $crawler->filterXPath('//input')->count());

        $form                                  = $crawler->filterXpath('//button[@type="submit"]')->form();
        $form['natue_userbundle_group[name]']  = 'Name #' . uniqid();
        $form['natue_userbundle_group[roles]'] = ['ROLE_ADMIN'];

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Created') . '")')->count()
        );
    }

    public function testInvalidCreateAction()
    {
        $client = self::$client;

        // Group name is unique
        $invalidGroupName = $this->groupFactory()->getName();

        $crawler = $client->request('GET', self::$router->generate('user_group_create'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(2, $crawler->filterXPath('//input')->count());

        $form                                  = $crawler->filterXpath('//button[@type="submit"]')->form();
        $form['natue_userbundle_group[name]']  = $invalidGroupName;
        $form['natue_userbundle_group[roles]'] = ['ROLE_ADMIN'];

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Group name already exist') . '")')->count()
        );
    }

    /**
     * @return void
     */
    public function testUpdateAction()
    {
        $client = self::$client;
        $group  = $this->groupFactory();

        $crawler = $client->request('GET', self::$router->generate('user_group_update', ['id' => $group->getId()]));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(2, $crawler->filterXPath('//input')->count());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Update Group') . '")')->count()
        );

        $changeString = 'Changed ' . uniqid();

        $form                                 = $crawler->filterXpath('//button[@type="submit"]')->form();
        $form['natue_userbundle_group[name]'] = $changeString;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Updated') . '")')->count()
        );

        self::$entityManager->refresh($group);

        $this->assertEquals($changeString, $group->getName());
    }

    /**
     * @return void
     */
    public function testEmptyUpdateAction()
    {
        $client = self::$client;

        $this->setExpectedException('Symfony\Component\Routing\Exception\MissingMandatoryParametersException');

        $crawler = $client->request('GET', self::$router->generate('user_group_update'));
    }

    /**
     * @return void
     */
    public function testInvalidUpdateAction()
    {
        $client = self::$client;

        $group1 = $this->groupFactory();
        $group2 = $this->groupFactory();

        $invalidName = $group2->getName();

        $repository   = self::$entityManager->getRepository('NatueUserBundle:Group');
        $queryBuilder = $repository->createQueryBuilder('groupEntity')
            ->select('MAX(groupEntity.id)');
        $wrongGroupId = $queryBuilder->getQuery()->getSingleScalarResult() + 1;

        $crawler = $client->request('GET', self::$router->generate('user_group_update', ['id' => $wrongGroupId]));

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Not found') . '")')->count()
        );

        $crawler = $client->request('GET', self::$router->generate('user_group_update', ['id' => $group1->getId()]));

        $form                                 = $crawler->filterXpath('//button[@type="submit"]')->form();
        $form['natue_userbundle_group[name]'] = $invalidName;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Group name already exist') . '")')->count()
        );
    }

    /**
     * @return void
     */
    public function testDeleteAction()
    {
        $client = self::$client;
        $group  = $this->groupFactory();

        $crawler = $client->request('GET', self::$router->generate('user_group_delete', ['id' => $group->getId()]));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Deleted') . '")')->count()
        );
    }

    /**
     * @return void
     */
    public function testEmptyDeleteAction()
    {
        $client = self::$client;

        $this->setExpectedException('Symfony\Component\Routing\Exception\MissingMandatoryParametersException');

        $client->request('GET', self::$router->generate('user_group_delete'));
    }

    /**
     * @return void
     */
    public function testInvalidDeleteAction()
    {
        $client = self::$client;

        $repository   = self::$entityManager->getRepository('NatueUserBundle:Group');
        $queryBuilder = $repository->createQueryBuilder('groupEntity')
            ->select('MAX(groupEntity.id)');
        $wrongGroupId = $queryBuilder->getQuery()->getSingleScalarResult() + 1;

        $crawler = $client->request('GET', self::$router->generate('user_group_delete', ['id' => $wrongGroupId]));

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Not found') . '")')->count()
        );
    }
}
