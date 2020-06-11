<?php

namespace Natue\Bundle\InvoiceBundle\Taxman\Contracts;

/**
 * Interface ConnectorInterface
 * @package Natue\Bundle\InvoiceBundle\Taxman\Contracts
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
interface ConnectorInterface
{
    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxman\Contracts\ResourceInterface $resource
     * @param mixed                                                          $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function fetch(ResourceInterface $resource, $id);

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxman\Contracts\ResourceInterface  $resource
     * @param \Natue\Bundle\InvoiceBundle\Taxman\Contracts\ArrayableInterface $object
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create(ResourceInterface $resource, ArrayableInterface $object);
}
