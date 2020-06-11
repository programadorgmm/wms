<?php

namespace Natue\Bundle\InvoiceBundle\Tests\Taxman;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\InvoiceBundle\Taxman\Client;
use Natue\Bundle\InvoiceBundle\Taxman\Contracts\ConnectorInterface;
use Natue\Bundle\InvoiceBundle\Taxman\Resources\InvoiceResource;

/**
 * Class ClientTest
 * @package Natue\Bundle\InvoiceBundle\Tests\Taxman
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class ClientTest extends WebTestCase
{
    public function testItCanBeInstantiated()
    {
        $connector = $this->getMock(ConnectorInterface::class);
        $client = new Client($connector);

        $this->assertInstanceOf(Client::class, $client);
    }

    public function testItCanCreateAnInvoiceResourceInstante()
    {
        $connector = $this->getMock(ConnectorInterface::class);
        $client = new Client($connector);
        $invoiceResource = $client->invoice();

        $this->assertInstanceOf(InvoiceResource::class, $invoiceResource);
    }
}
