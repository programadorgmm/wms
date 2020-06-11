<?php

namespace Natue\Bundle\InvoiceBundle\Taxman\Resources;

use Natue\Bundle\InvoiceBundle\Taxman\Contracts\ConnectorInterface;
use Natue\Bundle\InvoiceBundle\Taxman\Contracts\InvoiceInterface;
use Natue\Bundle\InvoiceBundle\Taxman\Contracts\ResourceInterface;

/**
 * Class InvoiceResource
 * @package Natue\Bundle\InvoiceBundle\Taxman\Resources
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class InvoiceResource implements ResourceInterface
{
    /**
     * @var \Natue\Bundle\InvoiceBundle\Taxman\Contracts\ConnectorInterface
     */
    protected $connector;

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxman\Contracts\ConnectorInterface $connector
     */
    public function __construct(ConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    /**
     * @param int $invoiceCode
     * @return array
     */
    public function fetch($invoiceCode)
    {
        $response = $this->connector->fetch($this, $invoiceCode);
        
        return json_decode($response->getBody(), true);
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxman\Contracts\InvoiceInterface $invoice
     * @return void
     */
    public function create(InvoiceInterface $invoice)
    {
        $this->connector->create($this, $invoice);
    }

    /**
     * @return string
     */
    public function getResourceName()
    {
        return 'invoice';
    }
}
