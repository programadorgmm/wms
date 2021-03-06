<?php

namespace Natue\Bundle\ZedBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ZedSupplierBarcodeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ZedSupplierBarcodeRepository extends EntityRepository
{
    public function findByBarcodeAndSupplier($barcode, $supplier)
    {
        $query = $this->createQueryBuilder('zedSupplierBarcode')
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'zedProduct.id = zedSupplierBarcode.zedProduct'
            )
            ->where('zedProduct.zedSupplier = :supplier')
            ->andWhere('zedSupplierBarcode.barcode = :barcode')
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
