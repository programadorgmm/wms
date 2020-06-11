<?php

namespace Natue\Bundle\ShippingBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Natue\Bundle\ZedBundle\Entity\PackedOrder;
use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;

class PackedOrderRepository extends EntityRepository
{
    public function getByUserZedOrder(User $user, ZedOrder $zedOrder)
    {
        $query = $this->createQueryBuilder('packedOrder')
            ->where('packedOrder.user = :userId')
            ->andWhere('packedOrder.zedOrder = :zedOrderId')
            ->setParameter('userId', $user)
            ->setParameter('zedOrderId', $zedOrder)
            ->setMaxResults(1)
            ->getQuery();

        return $query->getResult();
    }
}
