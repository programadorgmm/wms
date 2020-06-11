<?php

namespace Natue\Bundle\InvoiceBundle\Taxman;

use GuzzleHttp;
use Natue\Bundle\InvoiceBundle\Taxman\Contracts\ConnectorInterface;
use Natue\Bundle\InvoiceBundle\Taxman\Resources\InvoiceResource;

/**
 * Class Client
 * @package Natue\Bundle\InvoiceBundle\Taxman
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class Client
{
    /**
     * @var \Natue\Bundle\InvoiceBundle\Taxman\Contracts\ConnectorInterface
     */
    protected $connector;

    /**
     * @var \Natue\Bundle\InvoiceBundle\Taxman\Resources\InvoiceResource
     */
    protected $invoiceResource;

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxman\Contracts\ConnectorInterface $connector
     */
    public function __construct(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @return \Natue\Bundle\InvoiceBundle\Taxman\Resources\InvoiceResource
     */
    public function invoice()
    {
        return $this->invoiceResource ?: $this->invoiceResource = new InvoiceResource($this->connector);
    }
}
