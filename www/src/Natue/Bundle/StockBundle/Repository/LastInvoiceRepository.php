<?php

namespace Natue\Bundle\StockBundle\Repository;

use Natue\Bundle\StockBundle\Service\LastInvoiceSynchronizer;
use Predis\Client as RedisClient;

/**
 * Class LastInvoiceRepository
 *
 * @package Natue\Bundle\StockBundle\Repository
 */
class LastInvoiceRepository
{
    /**
     * @var RedisClient
     */
    private $redisClient;

    /**
     * LastInvoiceRepository constructor.
     *
     * @param RedisClient $redisClient
     */
    public function __construct(RedisClient $redisClient)
    {
        $this->redisClient = $redisClient;
    }

    /**
     * @param array $sku
     *
     * @return array
     */
    public function findBySku(array $sku)
    {
        if (empty($sku)) {
            return [];
        }

        $row = $this->redisClient->mget($this->prepareKeys($sku));
        $lastInvoiceList = array_map(function ($row) {
            return json_decode($row, true);
        }, $row);
        $skuList = array_map(function ($lastInvoice) {
            return $lastInvoice['sku'];
        }, $lastInvoiceList);

        return array_combine($skuList, $lastInvoiceList);
    }

    /**
     * @param array $sku
     *
     * @return array
     */
    protected function prepareKeys(array $sku)
    {
        $keys = array_map(function ($sku) {
            return LastInvoiceSynchronizer::prepareLastInvoiceKey($sku);
        }, $sku);

        return array_values($keys);
    }
}