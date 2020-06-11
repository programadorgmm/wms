<?php

namespace Natue\Bundle\InvoiceBundle\Tests\Taxman\Resources;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\InvoiceBundle\Taxman\Contracts\ConnectorInterface;
use Natue\Bundle\InvoiceBundle\Taxman\Contracts\InvoiceInterface;
use Natue\Bundle\InvoiceBundle\Taxman\Resources\InvoiceResource;
use Psr\Http\Message\ResponseInterface;

/**
 * Class InvoiceResourceTest
 * @package Natue\Bundle\InvoiceBundle\Tests\Taxman\Resources
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class InvoiceResourceTest extends WebTestCase
{
    public function testItCanBeInstantiated()
    {
        $connector = $this->getMock(ConnectorInterface::class);
        $invoiceResource = new InvoiceResource($connector);

        $this->assertInstanceOf(InvoiceResource::class, $invoiceResource);
    }

    public function testItFetchesAnInvoiceUsingAConnector()
    {
        $response = $this->getMock(ResponseInterface::class);
        $response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue(json_encode($expected = ['foo' => 'bar'])));

        $connector = $this->getMock(ConnectorInterface::class);
        $connector->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue($response));

        $invoiceResource = new InvoiceResource($connector);

        $this->assertEquals($expected, $invoiceResource->fetch(1));
    }

    public function testItCreatesAnInvoiceUsingAConnector()
    {
        $response = $this->getMock(ResponseInterface::class);

        $connector = $this->getMock(ConnectorInterface::class);
        $connector->expects($this->once())
            ->method('create')
            ->will($this->returnValue($response));

        $invoice = $this->getMock(InvoiceInterface::class);

        $invoiceResource = new InvoiceResource($connector);
        $invoiceResource->create($invoice);
    }

    public function testItCanTellYouItsName()
    {
        $connector = $this->getMock(ConnectorInterface::class);
        $invoiceResource = new InvoiceResource($connector);

        $this->assertEquals('invoice', $invoiceResource->getResourceName());
    }
}
