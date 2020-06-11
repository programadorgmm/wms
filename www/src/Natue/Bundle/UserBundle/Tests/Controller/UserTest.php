<?php

namespace Natue\Bundle\UserBundle\Tests\Controller;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * User test
 */
class UserTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testListAction()
    {
        $client = self::$client;

        $crawler = $client->request('GET', self::$router->generate('user_user_list'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(1, $crawler->filter('table.pedroteixeira-grid-table')->count());

        $client->request(
            'GET',
            self::$router->generate('user_user_list'),
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

        $group = $this->groupFactory();

        $crawler = $client->request('GET', self::$router->generate('user_user_create'));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(7, $crawler->filterXPath('//input')->count());
        $this->assertEquals(1, $crawler->filterXPath('//select')->count());

        $form                                                 = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();
        $form['natue_userbundle_user[username]']              = 'user#' . uniqid();
        $form['natue_userbundle_user[name]']                  = 'Name #' . uniqid();
        $form['natue_userbundle_user[email]']                 = uniqid() . '@email.com';
        $form['natue_userbundle_user[enabled]']               = true;
        $form['natue_userbundle_user[plainPassword][first]']  = 'test123';
        $form['natue_userbundle_user[plainPassword][second]'] = 'test123';
        $form['natue_userbundle_user[groups]']                = $group->getId();

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Created') . '")')->count()
        );
    }

    /**
     * @return void
     */
    public function testInvalidCreateAction()
    {
        $client = self::$client;

        $user  = $this->userFactory();
        $group = $this->groupFactory();
        self::$entityManager->persist($user);
        self::$entityManager->flush();

        $wrongUsername = $user->getUsername();

        $crawler = $client->request('GET', self::$router->generate('user_user_create'));

        $form                                                 = $crawler->filterXpath(
            '//button[@type="submit"]'
        )->form();
        $form['natue_userbundle_user[username]']              = $wrongUsername;
        $form['natue_userbundle_user[name]']                  = 'Name #' . uniqid();
        $form['natue_userbundle_user[email]']                 = uniqid() . '@email.com';
        $form['natue_userbundle_user[enabled]']               = true;
        $form['natue_userbundle_user[plainPassword][first]']  = 'test123';
        $form['natue_userbundle_user[plainPassword][second]'] = 'test123';
        $form['natue_userbundle_user[groups]']                = $group->getId();

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Username already exist') . '")')->count()
        );
    }

    /**
     * @return void
     */
    public function testUpdateAction()
    {
        $client = self::$client;
        $user   = $this->userFactory();

        $crawler = $client->request('GET', self::$router->generate('user_user_update', ['id' => $user->getId()]));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(8, $crawler->filterXPath('//input')->count());
        $this->assertEquals(1, $crawler->filterXPath('//select')->count());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Update User') . '")')->count()
        );

        $form                                = $crawler->filterXpath('//button[@type="submit"]')->form();
        $form['natue_userbundle_user[name]'] = 'Changed';

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Updated') . '")')->count()
        );

        self::$entityManager->refresh($user);

        $this->assertEquals('Changed', $user->getName());
    }

    /**
     * @return void
     */
    public function testInvalidUpdateAction()
    {
        $client = self::$client;

        $user1 = $this->userFactory();
        $user2 = $this->userFactory();
        self::$entityManager->persist($user1);
        self::$entityManager->flush();

        $wrongUsername = $user2->getUsername();

        $crawler = $client->request('GET', self::$router->generate('user_user_update', ['id' => $user1->getId()]));

        $form                                    = $crawler->filterXpath('//button[@type="submit"]')->form();
        $form['natue_userbundle_user[username]'] = $wrongUsername;

        $crawler = $client->submit($form);

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Username already exist') . '")')->count()
        );
    }

    /**
     * @return void
     */
    public function testEmptyUpdateAction()
    {
        $client = self::$client;

        $this->setExpectedException('Symfony\Component\Routing\Exception\MissingMandatoryParametersException');

        $client->request('GET', self::$router->generate('user_user_update'));
    }

    /**
     * @return void
     */
    public function testDeleteAction()
    {
        $client = self::$client;
        $user   = $this->userFactory();

        $crawler = $client->request('GET', self::$router->generate('user_user_delete', ['id' => $user->getId()]));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Deleted') . '")')->count()
        );
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
        $wrongUserId  = $queryBuilder->getQuery()->getSingleScalarResult() + 1;


        $crawler = $client->request('GET', self::$router->generate('user_user_delete', ['id' => $wrongUserId]));

        $this->assertFalse($client->getResponse()->isSuccessful());
        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Not found') . '")')->count()
        );
    }

    /**
     * @return void
     */
    public function testEmptyDeleteAction()
    {
        $client = self::$client;

        $this->setExpectedException('Symfony\Component\Routing\Exception\MissingMandatoryParametersException');

        $client->request('GET', self::$router->generate('user_user_delete'));
    }
}
