<?php

namespace Natue\Bundle\CoreBundle\Tests\Controller;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;

/**
 * Default Controller test
 */
class DefaultTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testIndex()
    {
        $client = self::$client;

        $crawler = $client->request('GET', self::$router->generate('homepage'));

        $this->assertEquals(
            1,
            $crawler->filter('html:contains("' . self::$translator->trans('Welcome to WMS') . '")')->count()
        );
    }

    /**
     * @return void
     */
    public function testLogin()
    {
        $client = self::$client;

        $crawler = $client->request('GET', self::$router->generate('fos_user_security_login'));

        $this->assertEquals(1, $crawler->filterXPath('//form')->count());

        $this->assertEquals(3, $crawler->filterXPath('//input')->count());
    }

    /**
     * @return void
     */
    public function testLanguage()
    {
        $client = self::$client;

        $client->request('GET', self::$router->generate('language'));

        $this->assertTrue($client->getResponse()->isSuccessful());

        $client->request(
            'GET',
            self::$router->generate('language'),
            array(),
            array(),
            array('HTTP_REFERER' => '')
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}
