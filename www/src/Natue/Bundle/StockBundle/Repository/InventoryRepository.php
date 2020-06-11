<?php

namespace Natue\Bundle\StockBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Natue\Bundle\StockBundle\Entity\StockPosition as StockPositionEntity;

/**
 * Class InventoryRepository
 *
 * @package Natue\Bundle\StockBundle\Repository
 */
class InventoryRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getInventoriesForListing()
    {
        return $this->createQueryBuilder('inventory')
            ->addSelect('inventory.id')
            ->addSelect('inventory.finishedAt')
            ->addSelect('stockPosition.name as stockPositionName')
            ->addSelect('user.name as userName')
            ->innerJoin('inventory.stockPosition', 'stockPosition')
            ->innerJoin('inventory.user', 'user')
            ->orderBy('inventory.id', 'desc')
            ;
    }

    /**
     * @param StockPositionEntity $stockPositionEntity
     *
     * @return array
     */
    public function getGetInventoriesNotFinished(StockPositionEntity $stockPositionEntity)
    {
        return $this->createQueryBuilder('inventory')
            ->select('inventory.id')
            ->addSelect('inventory.finishedAt')
            ->innerJoin('inventory.stockPosition', 'stockPosition')
            ->andWhere('inventory.finishedAt IS NULL')
            ->andWhere('stockPosition.id = :stockPositionId')
            ->getQuery()
            ->setParameters([
                    'stockPositionId' => $stockPositionEntity->getId()
                ])
            ->getResult();
    }

    /**
     * @param int $userId
     *
     * @return array
     */
    public function getInventoriesNotFinishedByUserId($userId)
    {
        return $this->findBy(
            [
                'user' => $userId,
                'finishedAt' => null
            ]
        );
    }
}
