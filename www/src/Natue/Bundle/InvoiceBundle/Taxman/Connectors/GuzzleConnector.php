<?php

namespace Natue\Bundle\InvoiceBundle\Taxman\Connectors;

use GuzzleHttp;
use Natue\Bundle\InvoiceBundle\Taxman\Contracts\ArrayableInterface;
use Natue\Bundle\InvoiceBundle\Taxman\Contracts\ConnectorInterface;
use Natue\Bundle\InvoiceBundle\Taxman\Contracts\ResourceInterface;

/**
 * Class GuzzleConnector
 * @package Natue\Bundle\InvoiceBundle\Taxman\Connectors
 * @author Rafael Willians <rafael.willians@natue.com.br>
 */
class GuzzleConnector implements ConnectorInterface
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzle;

    /**
     * @param string $uri
     */
    public function __construct($uri)
    {
        $this->guzzle = new GuzzleHttp\Client([
            'base_uri' => rtrim($uri, '/') . '/',
        ]);
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxman\Contracts\ResourceInterface $resource
     * @param mixed                                                          $id
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function fetch(ResourceInterface $resource, $id)
    {
        return $this->guzzle->get(sprintf('%s/%s', $resource->getResourceName(), $id));
    }

    /**
     * @param \Natue\Bundle\InvoiceBundle\Taxman\Contracts\ResourceInterface  $resource
     * @param \Natue\Bundle\InvoiceBundle\Taxman\Contracts\ArrayableInterface $object
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create(ResourceInterface $resource, ArrayableInterface $object)
    {
        return $this->guzzle->post($resource->getResourceName(), ['json' => $object->toArray()]);
    }
}
