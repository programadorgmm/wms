<?php
namespace Natue\Bundle\StockBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for OrderRequest
 */
class OrderRequestRepository extends EntityRepository
{
    public function searchByLongName($text)
    {
        return $this->createQueryBuilder('orderRequest')
            ->select('orderRequest.id as id')
            ->addSelect('concat(orderRequest.id, \' - \', orderRequest.description) as text')
            ->where('orderRequest.description like :description')
            ->orWhere('orderRequest.id like :id')
            ->getQuery()
            ->setParameters([
                'description' => '%' . $text . '%',
                'id' => $text,
            ])
            ->getResult();
    }
}