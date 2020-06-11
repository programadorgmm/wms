<?php

namespace Natue\Bundle\StockBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Natue\Bundle\StockBundle\Entity\OrderRequest;


class OrderRequestItemRepository extends EntityRepository
{
    /**
     * @param OrderRequest $orderRequest
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countItemsAtOrderRequest(OrderRequest $orderRequest)
    {
        return $this->createQueryBuilder('orderRequestItem')
            ->select('sum(orderRequestItem.quantity) as total')
            ->where('orderRequestItem.orderRequest = :id')
            ->getQuery()
            ->setParameter('id', $orderRequest->getId())
            ->getSingleResult()['total'];
    }
}