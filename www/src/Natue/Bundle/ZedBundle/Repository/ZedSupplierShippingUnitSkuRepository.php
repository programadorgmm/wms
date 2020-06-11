<?php

namespace Natue\Bundle\ZedBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ZedSupplierShippingUnitSkuRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ZedSupplierShippingUnitSkuRepository extends EntityRepository
{
    public function findBySkuAndSupplier($sku, $supplier)
    {
        $query = $this->createQueryBuilder('zedSupplierShippingUnitSku')
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'zedProduct.id = zedSupplierShippingUnitSku.zedProduct'
            )
            ->where('zedProduct.zedSupplier = :supplier')
            ->andWhere('zedSupplierShippingUnitSku.sku = :sku')
            ->setParameters(
                [
                    'sku' => $sku,
                    'supplier' => $supplier,
                ]
            )
            ->getQuery();

        return $query->getResult();
    }
}
