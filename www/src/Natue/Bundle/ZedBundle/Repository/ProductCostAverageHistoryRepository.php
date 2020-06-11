<?php

namespace Natue\Bundle\ZedBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Natue\Bundle\ZedBundle\Entity\ZedProduct;
use Natue\Bundle\ZedBundle\Entity\ProductCostAverageHistory;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItemReception;

/**
 * @package Natue\Bundle\ZedBundle\Repository
 */
class ProductCostAverageHistoryRepository extends EntityRepository
{
    public function getForPurchaseOrderItemReceptionZedProduct(
        PurchaseOrderItemReception $reception,
        ZedProduct $product
    ) {
        $queryBuilder = $this->createQueryBuilder('productCostAverageHistory')
            ->where('productCostAverageHistory.purchaseOrderItemReception = :reception')
            ->andWhere('productCostAverageHistory.zedProduct = :zedProduct')
            ->setMaxResults(1)
            ->setParameters(
                [
                    'reception' => $reception,
                    'zedProduct'   => $product,
                ]
            );

        return $queryBuilder->getQuery()->getSingleResult();
    }

    public function getAllNewestStartingFrom(ProductCostAverageHistory $averageCost, ZedProduct $product)
    {
        $queryBuilder = $this->createQueryBuilder('productCostAverageHistory')
            ->where('productCostAverageHistory.id > :averageCost')
            ->andWhere('productCostAverageHistory.zedProduct = :zedProduct')
            ->orderBy('productCostAverageHistory.id', 'ASC')
            ->setParameters(
                [
                    'averageCost' => $averageCost->getId(),
                    'zedProduct'   => $product,
                ]
            );

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get by DateTime QueryBuilder
     *
     * @param \DateTime $dateTime
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getByDateTimeQueryBuilder(\DateTime $dateTime)
    {
        $dateStart = $dateTime->format('Y-m-d');
        $dateEnd   = (new \DateTime($dateStart))
                        ->add(\DateInterval::createFromDateString('1 day'))
                        ->format('Y-m-d');

        $queryBuilder = $this->createQueryBuilder('productCostAverageHistory')
            ->addSelect('zedProduct.sku AS sku')
            ->addSelect('zedProduct.name AS name')
            ->addSelect('productCostAverageHistory.costAverage AS costAverage')
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'productCostAverageHistory.zedProduct = zedProduct.id'
            )
            ->where('productCostAverageHistory.createdAt >= :dateStart')
            ->andWhere('productCostAverageHistory.createdAt < :dateEnd')
            ->setParameters(
                [
                    'dateStart' => $dateStart,
                    'dateEnd'   => $dateEnd,
                ]
            );

        return $queryBuilder;
    }

    /**
     * Get logs by DateTime
     *
     * @param \DateTime $dateTime
     *
     * @return array
     */
    public function getByDateTime(\DateTime $dateTime)
    {
        $query = $this->getByDateTimeQueryBuilder($dateTime)->getQuery();

        return $query->getResult();
    }

    /**
     * Get average cost by zed_product
     *
     * @param ZedProduct $zedProduct
     *
     * @return int|null
     */
    public function getAverageCostByZedProduct(ZedProduct $zedProduct)
    {
        $query = $this->createQueryBuilder('productCostAverageHistory')
            ->select('productCostAverageHistory.costAverage AS cost')
            ->where('productCostAverageHistory.zedProduct = :zedProduct')
            ->orderBy('productCostAverageHistory.createdAt', 'DESC')
            ->setMaxResults(1)
            ->setParameter('zedProduct', $zedProduct)
            ->getQuery();

        $result = $query->getOneOrNullResult();

        return $result ? $result['cost'] : 0;
    }
}
