<?php

namespace Natue\Bundle\StockBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Natue\Bundle\StockBundle\Entity\OrderRequestItem;
use Natue\Bundle\StockBundle\Form\Model\OrderRequest as OrderRequestModel;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Natue\Bundle\StockBundle\Entity\OrderRequest as OrderRequestEntity;
use Symfony\Component\Security\Core\SecurityContext;
use Ddeboer\DataImport\Reader\CsvReader;

/**
 * Class OrderRequest
 * @package Natue\Bundle\StockBundle\Service
 */
class OrderRequest
{
    const DELIMITER = ';';
    const COLUMN_SKU = 'sku';
    const COLUMN_QUANTITY = 'quantity';
    const COLUMN_REQUESTED_INVOICE_COST = 'requested_invoice_cost';

    /**
     * @var array
     */
    protected static $csvHeaders = [self::COLUMN_SKU, self::COLUMN_QUANTITY];

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected $entityManager;

    /**
     * @var mixed
     */
    protected $user;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $zedProductRepository;

    /**
     * @param Registry $doctrine
     * @param SecurityContext $securityContext
     */
    public function __construct(Registry $doctrine, SecurityContext $securityContext)
    {
        $this->entityManager = $doctrine->getManager();
        $this->user = $securityContext->getToken()->getUser();
        $this->zedProductRepository = $doctrine->getRepository('NatueZedBundle:ZedProduct');
    }

    /**
     * @param OrderRequestModel $model
     * @return OrderRequestEntity
     * @throws
     * @throws \Exception
     */
    public function save(OrderRequestModel $model)
    {
        $orderRequest = new OrderRequestEntity();
        $orderRequest->setDescription($model->getDescription());
        $orderRequest->setZedSupplier($model->getSupplier());
        $orderRequest->setUser($this->user);

        $this->entityManager->persist($orderRequest);
        $this->entityManager->flush();

        $csvReader = $this->getCsvReaderInstance($model->getSubmitFile()->getPathname());
        $csvReader->setHeaderRowNumber(0);
        $this->validateCsvHeaders($csvReader, self::$csvHeaders);

        $items = $this->getItemsFromCsv($csvReader, $orderRequest);

        foreach ($items as $item) {
            $orderRequest->addItem($item);
        }

        $this->entityManager->merge($orderRequest);
        $this->entityManager->flush();

        return $orderRequest;
    }

    /**
     * @param $csvReader
     * @param $orderRequest
     * @return array
     * @throws \Exception
     */
    protected function getItemsFromCsv($csvReader, $orderRequest)
    {

        $items = new ArrayCollection();

        foreach ($csvReader as $row) {

            if (empty($row[self::COLUMN_QUANTITY]) || empty($row[self::COLUMN_SKU])) {
                continue;
            }

            $item = new OrderRequestItem();
            $item->setOrderRequest($orderRequest);
            $item->setQuantity($row[self::COLUMN_QUANTITY]);

            if (!empty($row[self::COLUMN_REQUESTED_INVOICE_COST])) {
                $item->setRequestedInvoiceCost($row[self::COLUMN_REQUESTED_INVOICE_COST]);
            }

            $product = $this->zedProductRepository->findOneBySku($row[self::COLUMN_SKU]);

            if (!$product) {
                $this->entityManager->delete($orderRequest);
                $this->entityManager->flush();

                throw new \Exception('Sku not registered at ZED');
            }

            $item->setZedProduct($product);
            $items->add($item);
        }

        return $items;
    }

    /**
     * @param $csvPath
     *
     * @return CsvReader
     */
    protected function getCsvReaderInstance($csvPath)
    {
        return new CsvReader(new \SplFileObject($csvPath), self::DELIMITER);
    }

    /**
     * Check that the headers are as expected, otherwise throw an exception
     *
     * @param CsvReader $csvReader
     * @param array $csvColumnNames
     *
     * @throws \Exception
     * @return void
     */
    protected function validateCsvHeaders($csvReader, $csvColumnNames)
    {
        $countCsvColumnNames = count($csvColumnNames);

        for ($i = 0; $i < $countCsvColumnNames; $i++) {
            if ($csvReader->getColumnHeaders()[$i] != $csvColumnNames[$i]) {
                throw new \Exception(
                    'Column number:' . $i . ' (' . $csvReader->getColumnHeaders()[$i] .
                    ') is not "' . $csvColumnNames[$i] . '" as expected'
                );
            }
        }
    }
}
