<?php

namespace Natue\Bundle\StockBundle\Service;

use Doctrine\ORM\EntityManager;

use Natue\Bundle\StockBundle\Entity\StockPosition;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;

/**
 * Batch processing for StockItem
 * See: http://docs.doctrine-project.org/en/2.0.x/reference/batch-processing.html
 */
class BatchProcessingStockItem
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     *
     * @return BatchProcessingStockItem
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Update the barcode and/or date expiration
     *
     * @param ZedProduct    $zedProduct
     * @param StockPosition $stockPosition
     * @param string        $status
     * @param \DateTime     $dateExpiration
     * @param string        $barcode
     * @param \DateTime     $newDateExpiration
     * @param string        $newBarcode
     *
     * @return void
     */
    public function bulkUpdate(
        ZedProduct $zedProduct,
        StockPosition $stockPosition,
        $status,
        \DateTime $dateExpiration,
        $barcode,
        \DateTime $newDateExpiration,
        $newBarcode
    ) {
        $this->entityManager->createQueryBuilder()
            ->update('NatueStockBundle:StockItem', 'stockItem')
            ->set('stockItem.barcode', ':newBarcode')
            ->set('stockItem.dateExpiration', ':newDateExpiration')
            ->where('stockItem.zedProduct = :zedProduct')
            ->andWhere('stockItem.stockPosition = :stockPosition')
            ->andWhere('stockItem.status = :status')
            ->andWhere('stockItem.dateExpiration = :dateExpiration')
            ->andWhere('stockItem.barcode = :barcode')
            ->getQuery()
            ->setParameters(
                [
                    'zedProduct'        => $zedProduct,
                    'stockPosition'     => $stockPosition,
                    'status'            => $status,
                    'dateExpiration'    => $dateExpiration,
                    'barcode'           => $barcode,
                    'newDateExpiration' => $newDateExpiration,
                    'newBarcode'        => $newBarcode
                ]
            )
            ->execute();
    }
}
