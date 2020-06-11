<?php

namespace Natue\Bundle\StockBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class InventoryItemRepository extends EntityRepository
{
    /**
     * @param array $filters
     *
     * @return QueryBuilder
     */
    public function getQueryForListAction(array $filters = [])
    {
        $queryBuilder = $this->createQueryBuilder('inventoryItem')
            ->addSelect('COUNT(stockItem.barcode) AS qty')
            ->addSelect('inventory.id')
            ->addSelect('stockItem.barcode')
            ->addSelect('zedProduct.id AS zedProductId')
            ->addSelect('zedProduct.name AS productName')
            ->addSelect('inventoryItem.status AS inventoryItemStatus')
            ->innerJoin('inventoryItem.stockItem', 'stockItem')
            ->innerJoin('inventoryItem.inventory', 'inventory')
            ->leftJoin('stockItem.zedProduct', 'zedProduct')
            ->addGroupBy('stockItem.barcode')
            ->addGroupBy('inventoryItem.status')
            ->orderBy('inventoryItem.status', 'desc')
        ;

        if (isset($filters['inventoryId'])) {
            $queryBuilder
                ->andWhere('inventory.id = :inventoryId')
                ->setParameter('inventoryId', $filters['inventoryId']);
        }

        return $queryBuilder;
    }

    /**
     * @param array $filters
     *
     * @return array|null
     */
    public function getInventoryItemsGrouped(array $filters = [])
    {
        return $this->getQueryForListAction($filters)->getQuery()->execute();
    }
}
