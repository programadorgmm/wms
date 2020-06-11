<?php

namespace Natue\Bundle\ZedBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ZedSupplierShippingUnitBarcodeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ZedSupplierShippingUnitBarcodeRepository extends EntityRepository
{
    public function findByBarcodeAndSupplier($barcode, $supplier)
    {
        $query = $this->createQueryBuilder('zedSupplierShippingUnitBarcode')
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'zedProduct.id = zedSupplierShippingUnitBarcode.zedProduct'
            )
            ->where('zedProduct.zedSupplier = :supplier')
            ->andWhere('zedSupplierShippingUnitBarcode.barcode = :barcode')
            ->setParameters(
                [
                    'barcode' => $barcode,
                    'supplier' => $supplier,
                ]
            )
            ->getQuery();

        return $query->getResult();
    }
}
