<?php

namespace Natue\Bundle\ZedBundle\Service;

use Symfony\Component\HttpFoundation\Response;

use Guzzle\Http\Client as GuzzleClient;
use Guzzle\Http\Message\Response as GuzzleResponse;

/**
 * Class HttpClientAbstract
 * @package Natue\Bundle\ZedBundle\Service
 */
abstract class HttpClientAbstract
{

    const AUTH_EMAIL = 'wms@natue.com.br';

    const MESSAGE_SUCCESS = 'success';

    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $zedHostName;

    /**
     * @param GuzzleClient $client
     * @param string       $zedHostName
     *
     * @return HttpClientAbstract
     */
    public function __construct(GuzzleClient $client, $zedHostName)
    {
        $this->client = $client;
        $this->zedHostName = $zedHostName;
    }

    /**
     * @return string
     */
    protected function getZedHostName()
    {
        return $this->zedHostName;
    }

    /**
     * Build GET Request. Append Authentication token to query string
     *
     * @param $uri
     * @param array $query
     *
     * @return \Guzzle\Http\Message\RequestInterface
     */
    protected function getRequest($uri, array $query = [])
    {
        $query['query']['authToken'] = $this->getAuthToken();
        $query['verify'] = false;

        return $this->client->get(
            sprintf("%s/%s", $this->getZedHostName(), $uri),
            null,
            $query
        );
    }

    /**
     * Build POST Request. Set Authentication token on query string
     *
     * @param string $uri
     * @param string $postBody
     *
     * @return \Guzzle\Http\Message\RequestInterface
     */
    protected function postRequest($uri, $postBody)
    {
        $options = [
            'query' => ['authToken' => $this->getAuthToken()],
            'verify' => false
        ];

        return $this->client->post(
            sprintf("%s/%s", $this->getZedHostName(), $uri),
            null,
            $postBody,
            $options
        );
    }

    /**
     * Check, if the response is successful
     *
     * @param GuzzleResponse $response
     * @return bool
     */
    protected function isSuccess(GuzzleResponse $response)
    {
        if ($response->getStatusCode() == Response::HTTP_OK) {

            /**
             * Extend the response check, once
             * ZED Api would be refactored
             */

            return true;
        }

        return false;
    }

    /**
     * Get Authentication token
     *
     * @return string
     */
    protected function getAuthToken()
    {
        return md5(date('Y-m-d') . self::AUTH_EMAIL);
    }
}
