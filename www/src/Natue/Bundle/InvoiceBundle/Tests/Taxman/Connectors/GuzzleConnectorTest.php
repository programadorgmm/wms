<?php

namespace Natue\Bundle\InvoiceBundle\Tests\Taxman\Connectors;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\InvoiceBundle\Taxman\Connectors\GuzzleConnector;
use Natue\Bundle\InvoiceBundle\Taxman\Contracts\ArrayableInterface;
use Natue\Bundle\InvoiceBundle\Taxman\Contracts\ResourceInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class GuzzleConnectorTest
 * @package Natue\Bundle\InvoiceBundle\Tests\Taxman\Connectors
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class GuzzleConnectorTest extends WebTestCase
{
    /**
     * @dataProvider serviceParameters
     * @param string $uri
     * @return void
     */
    public function testItCanBeInstantiated($uri)
    {
        $guzzleConnector = new GuzzleConnector($uri);

        $this->assertInstanceOf(GuzzleConnector::class, $guzzleConnector);
    }

    /**
     * @dataProvider serviceParameters
     * @param string $uri
     * @return void
     */
    public function testItCanFetchARestResourceById($uri)
    {
        $guzzleConnector = new GuzzleConnector($uri);

        $resource = $this->getMockBuilder(ResourceInterface::class)->getMock();
        $resource->expects($this->once())
            ->method('getResourceName')
            ->will($this->returnValue('status'));

        $response = $guzzleConnector->fetch($resource, 200);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @dataProvider serviceParameters
     * @param string $uri
     * @return void
     */
    public function testItCanCreateEntryIntoRestResource($uri)
    {
        $guzzleConnector = new GuzzleConnector($uri);

        $resource = $this->getMockBuilder(ResourceInterface::class)->getMock();
        $resource->expects($this->once())
            ->method('getResourceName')
            ->will($this->returnValue('post'));

        $entry = $this->getMockBuilder(ArrayableInterface::class)->getMock();
        $entry->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($expected = []));

        $response = $guzzleConnector->create($resource, $entry);
        $responseArray = json_decode($response->getBody(), true);

        $this->assertEquals([], $responseArray['json']);
    }

    public function serviceParameters()
    {
        return [[
            'uri' => 'http://httpbin.org'
        ]];
    }
}
