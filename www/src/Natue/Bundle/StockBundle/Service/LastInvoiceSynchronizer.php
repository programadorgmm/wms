<?php

namespace Natue\Bundle\StockBundle\Service;

use Doctrine\DBAL\Connection;
use Predis\Client as RedisClient;

/**
 * Last Invoice sync service
 */
class LastInvoiceSynchronizer
{
    /**
     * @var $wmsConnection Connection
     */
    protected $wmsConnection;

    /**
     * @var $redisClient RedisClient
     */
    protected $redisClient;

    protected $bonificatedCfops = [
        '5910',
        '6910',
        '5911',
        '6911',
    ];

    /**
     * @var integer
     */
    const DEVOLUTION_SUPPLIER = 2411;

    /**
     * Initialize databases connections
     *
     * @param Connection $wmsConnection
     * @param RedisClient $redisClient
     *
     * @return LastInvoiceSynchronizer
     */
    public function __construct(Connection $wmsConnection, RedisClient $redisClient)
    {
        $this->wmsConnection = $wmsConnection;
        $this->redisClient = $redisClient;
    }

    /**
     * @return integer
     */
    public function synchronize()
    {
        $data = $this->prepareDataToSetOnStorage($this->getLastInvoices());

        $this->redisClient->mset($data);

        return count($data);
    }

    /**
     * @param array $rows
     * @return array
     */
    protected function prepareDataToSetOnStorage(array $rows)
    {
        return array_combine(
            $this->prepareKeys($rows),
            $this->prepareValues($rows)
        );
    }

    /**
     * @param array $rows
     * @return array
     */
    protected function prepareKeys(array $rows)
    {
        return array_map(function (array $row) {
           return self::prepareLastInvoiceKey($row['sku']);
        }, $rows);
    }

    /**
     * @param $sku
     * @return string
     */
    public static function prepareLastInvoiceKey($sku)
    {
        return sprintf('last_invoice:%s', $sku);
    }

    /**
     * @param array $rows
     * @return array
     */
    protected function prepareValues(array $rows)
    {
        return array_map(function (array $row) {
           return json_encode($row);
        }, $rows);
    }

    /**
     * @return array
     */
    protected function getLastInvoices()
    {
        $qb = $this->wmsConnection->createQueryBuilder()
            ->select(
                'zed_product.sku',
                'zed_product.ncm',
                'cost_history.cost_average as last_avg_cost',
                'purchase_order_item.cost as last_cost',
                'purchase_order_item.invoice_cost as last_invoice_cost',
                'purchase_order_product.cfop',
                'purchase_order.invoice_key as last_key',
                'stock.created_at as last_received',
                'stock.zed_product'
            )
            ->from('zed_product', 'zed_product')
            ->innerJoin('zed_product', '(' . $this->getLastCostAverageQueryBuilder()->getSQL() . ')', 'last_cost_average', 'zed_product.id = last_cost_average.zed_product')
            ->leftJoin('last_cost_average', 'product_cost_average_history', 'cost_history', 'last_cost_average.last_id = cost_history.id')
            ->leftJoin('cost_history', '(' . $this->getLastTimeInStockQueryBuilder()->getSQL() . ')', 'last_time_in_stock', 'last_time_in_stock.product = cost_history.zed_product')
            ->leftJoin('last_time_in_stock', 'stock_item', 'stock', 'stock.id = last_time_in_stock.last_id')
            ->leftJoin('stock', 'purchase_order_item', 'purchase_order_item', 'purchase_order_item.id = stock.purchase_order_item')
            ->leftJoin('purchase_order_item', 'purchase_order_product', 'purchase_order_product', 'purchase_order_product.id = purchase_order_item.purchase_order_product')
            ->leftJoin('purchase_order_item', 'purchase_order', 'purchase_order', 'purchase_order.id = purchase_order_item.purchase_order')
            ->groupBy('cost_history.zed_product')
            ->setParameter(0, self::DEVOLUTION_SUPPLIER)
            ->setParameter(1, self::DEVOLUTION_SUPPLIER)
        ;

        $statement = $qb->execute();

        return $statement->fetchAll();
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function getLastCostAverageQueryBuilder()
    {
        return
            $this->wmsConnection->createQueryBuilder()
                ->select(
                    'product_cost_average_history.zed_product',
                    'MAX(product_cost_average_history.id) as last_id'
                )
                ->from('product_cost_average_history')
                ->leftJoin('product_cost_average_history', 'purchase_order_item', 'purchase_order_item', 'purchase_order_item.purchase_order_item_reception = product_cost_average_history.purchase_order_item_reception')
                ->leftJoin('purchase_order_item', 'purchase_order', 'purchase_order', 'purchase_order.id = purchase_order_item.purchase_order')
                ->where('purchase_order.zed_supplier != ?')
                ->groupBy('product_cost_average_history.zed_product');
    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function getLastTimeInStockQueryBuilder()
    {
        return
            $this->wmsConnection->createQueryBuilder()
                ->select(
                    'stock_item.zed_product as product',
                    'MAX(stock_item.id) as last_id'
                )
                ->from('stock_item')
                ->leftJoin('stock_item', 'purchase_order_item', 'purchase_order_item', 'stock_item.purchase_order_item = purchase_order_item.id')
                ->leftJoin('purchase_order_item', 'purchase_order', 'purchase_order', 'purchase_order.id = purchase_order_item.purchase_order')
                ->leftJoin('purchase_order_item', 'purchase_order_product', 'purchase_order_product', 'purchase_order_product.id = purchase_order_item.purchase_order_product')
                ->where('purchase_order.zed_supplier != ?')
                ->andWhere($this->wmsConnection->createQueryBuilder()->expr()->notIn('purchase_order_product.cfop', $this->bonificatedCfops))
                ->groupBy('stock_item.zed_product');
    }
}
