<?php

namespace Natue\Bundle\StockBundle\Service;

use Ddeboer\DataImport\Reader\CsvReader;
use Doctrine\ORM\EntityManager;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItem;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderProduct;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * PurchaseOrderItemCsv
 */
class PurchaseOrderItemCsv
{
    const DELIMITER = ';';
    const ROW_QUANTITY = 'QUANTITY';
    const ROW_COST = 'COST';
    const ROW_SKU = 'SKU';
    const ROW_ICMS = 'ICMS';
    const ROW_ICMS_ST = 'ICMS_ST';
    const ROW_INVOICE_COST = 'INVOICE_COST';
    const ROW_NFE_ORDEM = 'NFE_ORDEM';
    const ROW_NCM = 'NCM';
    const ROW_CST_PIS = 'CST_PIS';
    const ROW_CST_ICMS = 'CST_ICMS';
    const ROW_CFOP = 'CFOP';
    const ROW_SKU_SUPPLIER = 'SKU_SUPPLIER';

    /**
     * @var array
     */
    private static $headerColumns = [
        self::ROW_SKU,
        self::ROW_COST,
        self::ROW_QUANTITY,
        self::ROW_ICMS,
        self::ROW_ICMS_ST,
        self::ROW_INVOICE_COST,
        self::ROW_NFE_ORDEM,
        self::ROW_NCM,
        self::ROW_CST_PIS,
        self::ROW_CST_ICMS,
        self::ROW_CFOP,
        self::ROW_SKU_SUPPLIER,
    ];

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var BatchProcessingPurchaseOrderItem
     */
    protected $batchProcessingPurchaseOrderItem;

    /**
     * @param FormFactory $formFactory
     * @param ValidatorInterface $validator
     * @param BatchProcessingPurchaseOrderItem $batchProcessingPurchaseOrderItem
     *
     * @return PurchaseOrderItemCsv
     */
    public function __construct(
        FormFactory $formFactory,
        ValidatorInterface $validator,
        BatchProcessingPurchaseOrderItem $batchProcessingPurchaseOrderItem
    )
    {
        $this->formFactory = $formFactory;
        $this->validator = $validator;
        $this->batchProcessingPurchaseOrderItem = $batchProcessingPurchaseOrderItem;
    }

    /**
     * check if the csv file uploaded is valid, read the CSV,
     * check if the columns are valid, check if the rows are valid
     * and then insert these data in the database
     *
     * @param EntityManager $entityManager
     * @param string $id
     * @param string $csvPath
     *
     * @return void
     */
    public function processCsv($entityManager, $id, $csvPath)
    {
        $csvReader = $this->getCsvReaderInstance($csvPath);

        $csvReader->setHeaderRowNumber(0);

        // Test that we have for the first row the columns name: SKU,COST,QUANTITY
        $this->validateCsvHeaders($csvReader, self::$headerColumns);

        foreach ($csvReader as $row) {
            $this->validateCsvRowForPurchaseOrderItem($row);
            $this->insertDataFromCsvToDatabaseForPurchaseOrderItem($row, $id, $entityManager);
        }
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

    /**
     * Check if the row QUANTITY is >= 1 and a number
     *
     * @param array $row
     *
     * @throws \Exception
     * @return void
     */
    protected function validateCsvRowForPurchaseOrderItem($row)
    {
        if (!(ctype_digit($row[self::ROW_QUANTITY])) || ($row[self::ROW_QUANTITY] < 1)) {
            throw new \Exception('QUANTITY is not a number or less than 1');
        }
    }

    /**
     * Insert the row in the database
     *
     * @param array $row
     * @param string $id
     * @param EntityManager $entityManager
     *
     * @throws \Exception
     * @return void
     */
    protected function insertDataFromCsvToDatabaseForPurchaseOrderItem($row, $id, $entityManager)
    {
        /** @var \Natue\Bundle\ZedBundle\Entity\ZedProduct $zedProduct */
        $zedProduct = $entityManager->getRepository('NatueZedBundle:ZedProduct')->findOneBySku($row[self::ROW_SKU]);
        if (!$zedProduct) {
            throw new \Exception('SKU "' . $row[self::ROW_SKU] . '" not found');
        }

        /** @var \Natue\Bundle\StockBundle\Entity\PurchaseOrder $purchaseOrder */
        $purchaseOrder = $entityManager->getRepository('NatueStockBundle:PurchaseOrder')->findOneById($id);
        if (!$purchaseOrder) {
            throw new \Exception('Purchase order id "' . $id . '" not found');
        }

        $purchaseOrderProduct = $this->makePurchaseOrderProduct($entityManager, $purchaseOrder, $zedProduct, $row);
        $purchaseOrderItem = $this->getNewPurchaseOrderItem();

        $purchaseOrderItem->setCost($row[self::ROW_COST]);
        $purchaseOrderItem->setZedProduct($zedProduct);
        $purchaseOrderItem->setPurchaseOrder($purchaseOrder);
        $purchaseOrderItem->setIcmsSt($row[self::ROW_ICMS_ST]);
        $purchaseOrderItem->setIcms($row[self::ROW_ICMS]);
        $purchaseOrderItem->setInvoiceCost($row[self::ROW_INVOICE_COST]);
        $purchaseOrderItem->setPurchaseOrderProduct($purchaseOrderProduct);

        $this->batchProcessingPurchaseOrderItem->bulkInsert(
            $row[self::ROW_QUANTITY],
            $purchaseOrderItem
        );
    }

    /**
     * @param \Doctrine\ORM\EntityManager                    $entityManager
     * @param \Natue\Bundle\StockBundle\Entity\PurchaseOrder $purchaseOrder
     * @param \Natue\Bundle\ZedBundle\Entity\ZedProduct      $zedProduct
     * @param array                                          $row
     * @return \Natue\Bundle\StockBundle\Entity\PurchaseOrderProduct
     */
    protected function makePurchaseOrderProduct(
        EntityManager $entityManager,
        PurchaseOrder $purchaseOrder,
        ZedProduct $zedProduct,
        array $row
    ) {
        $purchaseOrderProduct = $this->getNewPurchaseOrderProduct();

        $purchaseOrderProduct->setPurchaseOrder($purchaseOrder);
        $purchaseOrderProduct->setZedProduct($zedProduct);
        $purchaseOrderProduct->setNfeSequential($row[self::ROW_NFE_ORDEM]);
        $purchaseOrderProduct->setNcm($row[self::ROW_NCM]);
        $purchaseOrderProduct->setCstPis($row[self::ROW_CST_PIS]);
        $purchaseOrderProduct->setCstIcms($row[self::ROW_CST_ICMS]);
        $purchaseOrderProduct->setCfop($row[self::ROW_CFOP]);

        if ($skuSupplier = trim($row[self::ROW_SKU_SUPPLIER])) {
            $purchaseOrderProduct->setSkuSupplier($skuSupplier);
        }

        $entityManager->persist($purchaseOrderProduct);

        return $purchaseOrderProduct;
    }

    /**
     * @return PurchaseOrderItem
     */
    protected function getNewPurchaseOrderItem()
    {
        return new PurchaseOrderItem();
    }

    /**
     * @return \Natue\Bundle\StockBundle\Entity\PurchaseOrderProduct
     */
    protected function getNewPurchaseOrderProduct()
    {
        return new PurchaseOrderProduct();
    }

    /**
     * @return File
     */
    protected function getFileConstraintInstance()
    {
        return new File();
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
}
