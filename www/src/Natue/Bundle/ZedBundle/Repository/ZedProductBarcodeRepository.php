<?php

namespace Natue\Bundle\ZedBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Natue\Bundle\ZedBundle\Entity\ZedProductBarcode;

/**
 * ZedProductBarcodeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 *
 * @method ZedProductBarcode findOneByBarcode($barcode)
 */
class ZedProductBarcodeRepository extends EntityRepository
{
    public function findByBarcodeAndSupplier($barcode, $supplier)
    {
        $query = $this->createQueryBuilder('zedProductBarcode')
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'zedProduct.id = zedProductBarcode.zedProduct'
            )
            ->where('zedProduct.zedSupplier = :supplier')
            ->andWhere('zedProductBarcode.barcode = :barcode')
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