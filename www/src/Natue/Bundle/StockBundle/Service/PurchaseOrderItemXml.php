<?php

namespace Natue\Bundle\StockBundle\Service;

use Doctrine\ORM\EntityNotFoundException;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderProduct;
use Natue\Bundle\StockBundle\Form\Model\XmlNotMatchedItem;
use Natue\Bundle\StockBundle\Form\Type\ChangeItemMultiplier;
use Natue\Bundle\StockBundle\Repository\LastInvoiceRepository;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\EntityManager;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItem;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Natue\Bundle\StockBundle\CST;

/**
 * PurchaseOrderItemXml
 */
class PurchaseOrderItemXml
{
    const PIS_ALIQUOT = 0.0925;

    const NOT_CHANGE_FLAG= 'not-change';
    const INCREASE_FLAG = 'increase';
    const DECREASE_FLAG = 'decrease';

    /**
     * @var array
     */
    public static $zeroAliquotPis = ['04', '05', '06', '07', '08', '09',];

    public static $icmsStTypes = [
        'ICMS70',
        'ICMS10',
        'ICMSSN101',
        'ICMSSN201',
        'ICMSSN202',
        'ICMSSN203',
    ];

    public static $icmsStCreditTypes = [
        'ICMSSN101',
    ];

    public static $bonificatedCfops = [
        '5910',
        '6910',
        '5911',
        '6911',
    ];

    public static $icmsTypes = [
        'ICMS00',
        'ICMS20',
        'ICMS60',
        'ICMSSN102',
        'ICMSSN500',
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
     * @var string
     */
    protected $xsdFile;

    /**
     * @var BatchProcessingPurchaseOrderItem
     */
    protected $batchProcessingPurchaseOrderItem;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected $entityManager;

    /**
     * @var LastInvoiceRepository
     */
    protected $lastInvoiceRepository;

    /**
     * @param FormFactory $formFactory
     * @param BatchProcessingPurchaseOrderItem $batchProcessingPurchaseOrderItem
     * @param string $xsdFile
     * @param Registry $doctrine
     * @param LastInvoiceRepository $lastInvoiceRepository
     *
     * @return PurchaseOrderItemXml
     */
    public function __construct(
        FormFactory $formFactory,
        BatchProcessingPurchaseOrderItem $batchProcessingPurchaseOrderItem,
        $xsdFile,
        Registry $doctrine,
        LastInvoiceRepository $lastInvoiceRepository
    ) {
        $this->formFactory = $formFactory;
        $this->xsdFile = $xsdFile;
        $this->batchProcessingPurchaseOrderItem = $batchProcessingPurchaseOrderItem;
        $this->entityManager = $doctrine->getEntityManager();
        $this->lastInvoiceRepository = $lastInvoiceRepository;
    }

    /**
     * check if the csv file uploaded is valid, read the CSV,
     * check if the columns are valid, check if the rows are valid
     * and then insert these data in the database
     *
     * @param string $xmlPath
     * @param int $supplier
     * @param bool $validate
     * @return array
     */
    public function processXml($xmlPath, $supplier, $shipping, $validate = true)
    {
        $document = $this->getXmlDocumentInstance($xmlPath);

        if ($validate) {
            $document->schemaValidate($this->xsdFile);
        }

        $rawXml = $document->saveXML();
        $xml = new \SimpleXMLElement($rawXml);

        $nfeTotalPrice = (float)$xml->NFe->infNFe->total->ICMSTot->vNF;
        $shippingPercentage = $shipping / $nfeTotalPrice;
        $order['shipping_percentage'] = $shippingPercentage;

        $order['total_price'] = $nfeTotalPrice;
        $order['not_matched'] = 0;
        $order['total_quantity'] = 0;
        $products = [];

        foreach ($xml->NFe->infNFe->det as $item) {
            $xmlProduct = $item->prod;

            $product = $this->findProductInfoByCProd((string) $xmlProduct->cProd, $supplier);

            if (!$product) {
                $product = $this->findProductInfoByBarcode((string) $xmlProduct->cEAN, $supplier);
            }

            if (!$product) {
                $product = $this->getEmptyProductData();
            }

            $product['nfe_sequential'] = (string) $item->attributes()->nItem;

            $product['supplier'] = [
                'sku' => (string)$xmlProduct->cProd,
                'ncm' => (string)$xmlProduct->NCM,
                'barcode' => (string)$xmlProduct->cEAN,
                'description' => (string)$xmlProduct->xProd,
                'quantity' => (float)$xmlProduct->qCom,
                'discount' => (float)$xmlProduct->vDesc,
                'price' => (float)$xmlProduct->vProd,
                'taxes' => json_decode(json_encode($item->imposto)),
                'cfop' => (string)$xmlProduct->CFOP,
                'shipping' => 0
            ];

            if ($shipping) {
                $product['supplier']['shipping'] = ((float) $xmlProduct->vProd) * $shippingPercentage;
            }

            if (property_exists($xmlProduct, 'vFrete')) {
                $product['supplier']['shipping'] = (float) $xmlProduct->vFrete;
            }

            if (!array_key_exists('multiplier', $product)) {
                $order['not_matched']++;
                $products[] = $product;

                continue;
            }

            if ($product['multiplier'] == 0) {
                $product['multiplier'] = 1;
            }

            $product['quantity'] = $product['supplier']['quantity'] * $product['multiplier'];

            $order['total_quantity'] += $product['quantity'];

            $product = $this->calculateProductInfo($product);

            $products[] = $product;
        }

        $this->addLastInvoiceOnProducts($products);
        $order['products'] = $products;

        return $order;
    }

    public function addLastInvoiceOnProducts(array &$products)
    {
        $skuList = array_map(function ($product) {
            return $product['zed_product_sku'];
        }, $products);
        $skuList = array_filter($skuList);


        $lastInvoiceList = $this->lastInvoiceRepository->findBySku($skuList);

        foreach ($products as &$product) {
            $sku = $product['zed_product_sku'];
            if (empty($sku) || !array_key_exists($sku, $lastInvoiceList)) {
                continue;
            }

            $product['last_invoice'] = $lastInvoiceList[$sku];
            $product['last_invoice']['last_cost'] = round($product['last_invoice']['last_cost'] / 100, 2);
            $product['last_invoice']['cost_diff'] = $this->calculateCostDiff(
                $product['last_invoice']['last_cost'],
                round($product['unit_cost'], 2)
            );
            $product['last_invoice']['cost_flag'] = $this->getCostDiffFlag(
                $product['last_invoice']['cost_diff']
            );

            $product['last_invoice']['last_invoice_cost'] = round($product['last_invoice']['last_invoice_cost'] / 100, 2);
            $product['last_invoice']['invoice_cost_diff'] = $this->calculateCostDiff(
                $product['last_invoice']['last_invoice_cost'],
                round($product['unit_price'], 2)
            );
            $product['last_invoice']['invoice_cost_flag'] = $this->getCostDiffFlag(
                $product['last_invoice']['invoice_cost_diff']
            );
        }

        return $products;
    }

    protected function getCostDiffFlag($costDiff)
    {
        if ($costDiff > 3) {
            return self::INCREASE_FLAG;
        }

        if ($costDiff < -3) {
            return self::DECREASE_FLAG;
        }

        return self::NOT_CHANGE_FLAG;
    }

    protected function calculateCostDiff($originalValue, $newValue)
    {
        if (!$originalValue) {
            return $newValue;
        }

        $diff = $newValue / $originalValue * 100;

        return round($diff - 100, 2);
    }

    public function calculateProductInfo($product)
    {
        $supplier = $product['supplier'];
        $taxes = $supplier['taxes'];

        $product['unit_ipi'] = $this->calculateIpi($taxes) / $product['quantity'];
        $product['unit_shipping_cost'] = $supplier['shipping'] / $product['quantity'];
        $product['unit_discount'] = $supplier['discount'] / $product['quantity'];
        $product['unit_price'] = $supplier['price'] / $product['quantity'];
        $product['unit_price_with_discount'] = $product['unit_price'] - $product['unit_discount'];
        $product['unit_icms_credit'] = $this->calculateIcmsCredit($taxes) / $product['quantity'];
        $product['unit_icms_to_save'] = $this->calculateIcms($taxes) / $product['quantity'];
        $product['unit_icms_to_save'] += $product['unit_icms_credit'];
        $product['unit_icms'] = $this->calculateIcms($taxes, false) / $product['quantity'];
        $product['unit_icms_st'] = $this->calculateIcmsSt($taxes) / $product['quantity'];
        $product['unit_pis_cofins'] = $this->calculatePisCofins($product, $taxes);
        $product['pis_cst'] = $this->calculatePisCst($taxes);
        $product['icms_st_calc_base'] = $this->getIcmsStCalcBase($taxes);
        $product['icms_cst'] = $this->calculateIcmsCst($taxes);
        $product['labels'] = $this->getProductLabels($product);

        if (in_array($product['supplier']['cfop'], self::$bonificatedCfops)) {
            $product['unit_cost'] = .01;

            return $product;
        }

        $product['unit_cost'] = round($this->calculateUnitCost($product), 2);

        return $product;
    }

    protected function getProductLabels($product)
    {
        return [
            'pis' => $this->getPisLabel($product),
            'icms' => $this->getIcmsLabel($product),
        ];
    }

    protected function getIcmsLabel($product)
    {
        $taxes = $product['supplier']['taxes'];
        $icmsCst = $this->calculateIcmsCst($taxes);
        $cstFactory = new CST\ICMS\Factory();

        return $cstFactory->create($icmsCst);
    }

    protected function calculateIpi($taxes)
    {
        if (!property_exists($taxes, 'IPI')) {
            return 0;
        }

        if (property_exists($taxes->IPI, 'IPITrib')) {
            return (float) $taxes->IPI->IPITrib->vIPI;
        }

        return 0;
    }

    protected function getPisLabel($product)
    {
        $taxes = $product['supplier']['taxes'];
        $pisCST = $this->calculatePisCst($taxes);
        $cstFactory = new CST\PIS\Factory();

        return $cstFactory->create((int) $pisCST);
    }

    protected function calculatePisCofins($product, $taxes)
    {
        if (in_array($this->calculatePisCst($taxes), self::$zeroAliquotPis)) {
            return 0;
        }

        return (
            $product['unit_price'] +
            $product['unit_ipi'] +
            $product['unit_shipping_cost'] -
            $product['unit_discount']
        ) * self::PIS_ALIQUOT;
    }

    /**
     * @param $taxes
     * @return int
     */
    protected function calculatePisCst($taxes)
    {
        if (!property_exists($taxes, 'PIS')) {
            return 0;
        }

        foreach ($taxes->PIS as $pis) {
            return (string) $pis->CST;
        }
    }

    /**
     * @param $taxes
     * @return float|int
     */
    protected function calculateIcmsCst($taxes)
    {
        if (!property_exists($taxes, 'ICMS')) {
            return 0;
        }

        $value = $this->getIcmsCst($taxes);

        if (!$value) {
            return $this->getIcmsCst($taxes, 'CSOSN');
        }

        return $value;
    }

    /**
     * @param $taxes
     * @param string $location
     * @return float|int
     */
    protected function getIcmsCst($taxes, $location = 'CST')
    {
        $value = $this->getTaxValueFrom($taxes, self::$icmsTypes, $location);

        if (!$value) {
            return $this->getTaxValueFrom($taxes, self::$icmsStTypes, $location);
        }

        return $value;
    }

    /**
     * @param $taxes
     * @param $types
     * @param $location
     * @return float|int
     */
    protected function getTaxValueFrom($taxes, $types, $location)
    {
        foreach ($types as $prefix) {
            if (!empty($taxes->ICMS->$prefix->$location)) {
                return (float)$taxes->ICMS->$prefix->$location;
            }
        }

        return 0;
    }

    /**
     * @param $taxes
     * @return float|int
     */
    protected function calculateIcmsSt($taxes)
    {
        return $this->getTaxValueFrom($taxes, self::$icmsStTypes, 'vICMSST');
    }

    /**
     * @param $taxes
     * @return float|int
     */
    protected function getIcmsStCalcBase($taxes)
    {
        return $this->getTaxValueFrom($taxes, self::$icmsStTypes, 'vBCST');
    }

    /**
     * @param $taxes
     * @param bool|true $withSt
     * @return float|int
     */
    protected function calculateIcms($taxes, $withSt = true)
    {
        $value = $this->getTaxValueFrom($taxes, self::$icmsTypes, 'vICMS');

        if ($withSt && !$value) {
            return $this->getTaxValueFrom($taxes, self::$icmsStTypes, 'vICMS');
        }

        return $value;
    }

    /**
     * @param $taxes
     * @return float|int
     */
    protected function calculateIcmsCredit($taxes)
    {
        return $this->getTaxValueFrom($taxes, self::$icmsStCreditTypes, 'vCredICMSSN');
    }

    /**
     * @param $product
     * @return mixed
     */
    protected function calculateUnitCost($product)
    {
        return (
            $product['unit_price'] +
            $product['unit_icms_st'] +
            $product['unit_ipi'] +
            $product['unit_shipping_cost'] -
            $product['unit_pis_cofins'] -
            $product['unit_icms'] -
            $product['unit_icms_credit'] -
            $product['unit_discount']
        );
    }

    /**
     * @param $barcode
     * @param $supplier
     * @return null
     */
    protected function findProductInfoByBarcode($barcode, $supplier)
    {
        $founds = $this->entityManager
            ->getRepository('NatueZedBundle:ZedProductBarcode')
            ->findByBarcode($barcode);

        if (!$this->validateItems($founds)) {
            $founds = $this->entityManager
                ->getRepository('NatueZedBundle:ZedSupplierBarcode')
                ->findByBarcode($barcode);
        }

        if (!$this->validateItems($founds)) {
           $founds = $this->entityManager
                ->getRepository('NatueZedBundle:ZedSupplierShippingUnitBarcode')
                ->findByBarcode($barcode);
        }

        if (!$this->validateItems($founds)) {
            return null;
        }

        $found = $this->filterItemBySupplier($founds, $supplier);

        if (!$found) {
            return null;
        }

        return $this->getItemData($found);
    }

    /**
     * @param $cProd
     * @param $supplier
     * @return array|null
     */
    protected function findProductInfoByCProd($cProd, $supplier)
    {
        $cProd = ltrim(trim(str_replace(['.', '-', ' '], '', $cProd)), '0');

        $founds = $this->entityManager
            ->getRepository('NatueZedBundle:ZedSupplierSku')
            ->findBySku($cProd);

        if (!$this->validateItems($founds)) {
            $founds = $this->entityManager
                ->getRepository('NatueZedBundle:ZedSupplierShippingUnitSku')
                ->findBySku($cProd);
        }

        if (!$this->validateItems($founds)) {
            return null;
        }

        if (count($founds) === 1) {
            return $this->getItemData(reset($founds));
        }

        $found = $this->filterItemBySupplier($founds, $supplier);

        if (!$found) {
            return null;
        }

        return $this->getItemData($found);
    }

    public function validateItems($items)
    {
        if (!$items) {
            return false;
        }

        foreach ($items as $item) {
            if ($this->validateItem($item)) {
                return true;
            }
        }

        return false;
    }

    public function validateItem($item)
    {
        try {
            $item->getZedProduct()->getSku();
            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    public function getItemData($item)
    {
        $data = $this->getEmptyProductData();

        try {
            /**
             * @var ZedProduct $zedProduct
             */
            $zedProduct = $item->getZedProduct();
            $averageCost = $this->entityManager
                ->getRepository('NatueZedBundle:ProductCostAverageHistory')
                ->getAverageCostByZedProduct($zedProduct);

            $data = array_replace(
                $data,
                    [
                    'zed_product_id' => $zedProduct->getId(),
                    'zed_product_name' => $zedProduct->getName(),
                    'zed_product_sku' => $zedProduct->getSku(),
                    'zed_product_ncm' => $zedProduct->getNcm(),
                    'zed_product_pis_cofins' => $zedProduct->getPisCofins(),
                    'zed_product_is_st' => $zedProduct->isSt(),
                    'zed_product_pis_label' => $zedProduct->getPisLabel(),
                    'zed_product_icms_label' => $zedProduct->getIcmsLabel(),
                    'zed_product_markup' => $zedProduct->getMarkup(),
                    'zed_product_cost' => round($zedProduct->getCost() / 100, 2),
                    'zed_product_average_cost' => round($averageCost / 100, 2),
                    'zed_product' => $zedProduct,
                    'multiplier' => 1
                ]
            );

            if (method_exists($item, 'getMultiplier') && $item->getMultiplier()) {
                $data['multiplier'] = $item->getMultiplier();
            }

            return $data;
        } catch (EntityNotFoundException $e) {
            return $data;
        }
    }

    protected function getEmptyProductData()
    {
        return [
            'zed_product_id' => null,
            'zed_product_name' => null,
            'zed_product_sku' => null,
            'zed_product_ncm' => null,
            'zed_product_pis_cofins' => null,
            'zed_product_is_st' => null,
            'zed_product_pis_label' => null,
            'zed_product_icms_label' => null,
            'zed_product' => null,
            'last_invoice' => [
                'last_invoice_cost' => null,
                'last_cost' => null,
                'last_key' => '',
                'cfop' => '',
                'invoice_cost_diff' => 0,
                'cost_diff' => 0
            ]
        ];
    }

    /**
     * @param array $items
     * @param $supplier
     * @return mixed|null
     */
    protected function filterItemBySupplier(array $items, $supplier)
    {
        if (count($items) === 1) {
            return current($items);
        }

        foreach ($items as $item) {
            try {
                if ($item->getZedProduct()->getZedSupplier()->getId() == $supplier) {
                    return $item;
                }
            } catch (EntityNotFoundException $e) {
                continue;
            }
        }

        return current($items);
    }

    /**
     * @param $product
     * @param $purchaseOrder
     */
    public function insertItem($product, $purchaseOrder)
    {
        $purchaseOrderItem = $this->getNewPurchaseOrderItem();
        $purchaseOrderItem->setCost($product['unit_cost'] * 100);
        $purchaseOrderItem->setZedProduct(
            $zedProduct = $this->entityManager->getRepository('NatueZedBundle:ZedProduct')->findOneById($product['zed_product_id'])
        );
        $purchaseOrderItem->setPurchaseOrder($purchaseOrder);
        $purchaseOrderItem->setPurchaseOrderProduct($this->createPurchaseOrderProduct($purchaseOrder, $zedProduct, $product));
        $purchaseOrderItem->setIcms($product['unit_icms_to_save'] * 100);
        $purchaseOrderItem->setIcmsStCalcBase($product['icms_st_calc_base'] * 100);
        $purchaseOrderItem->setIcmsSt($product['unit_icms_st'] * 100);
        $purchaseOrderItem->setInvoiceCost($product['unit_price_with_discount'] * 100);
        $purchaseOrderItem->setUpdatedAt($now = new \DateTime());
        $purchaseOrderItem->setCreatedAt($now);

        $this->batchProcessingPurchaseOrderItem->bulkInsert(
            $product['quantity'],
            $purchaseOrderItem
        );
    }

    /**
     * @param PurchaseOrder $purchaseOrder
     * @param ZedProduct $zedProduct
     * @param array $product
     * @return PurchaseOrderProduct
     */
    protected function createPurchaseOrderProduct(PurchaseOrder $purchaseOrder, ZedProduct $zedProduct, array $product)
    {
        $purchaseOrderProduct = new PurchaseOrderProduct();
        $supplier = $product['supplier'];

        $purchaseOrderProduct->setPurchaseOrder($purchaseOrder);
        $purchaseOrderProduct->setZedProduct($zedProduct);
        $purchaseOrderProduct->setSkuSupplier($supplier['sku']);

        // @todo validate these values
        $purchaseOrderProduct->setCstPis($product['pis_cst']);
        $purchaseOrderProduct->setCstIcms($product['icms_cst']);
        $purchaseOrderProduct->setCfop($supplier['cfop']);
        $purchaseOrderProduct->setNfeSequential($product['nfe_sequential']);
        $purchaseOrderProduct->setNcm($supplier['ncm']);

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
     * @param $xmlPath
     *
     * @return \DomDocument
     */
    protected function getXmlDocumentInstance($xmlPath)
    {
        $document = new \DOMDocument();
        $document->load($xmlPath);

        return $document;
    }

    /**
     * @param $products
     * @return array
     */
    public function getNotMatchedItems($products)
    {
        $items = [];

        foreach ($products as $key => $product) {
            if (array_key_exists('multiplier', $product)) {
                continue;
            }

            $items[] = (new XmlNotMatchedItem())
                ->setXmlCode($key)
                ->setNfeSequential($product['nfe_sequential'])
                ->setXmlDescription($product['supplier']['description'])
                ->setQuantity($product['supplier']['quantity'])
                ->setXmlQuantity($product['supplier']['quantity'])
            ;
        }

        return $items;
    }

    /**
     * @param $orderMeta
     * @return array
     */
    public function diffOrderRequest($orderMeta)
    {
        $inRequest = [];
        $notInRequest = [];
        $productIds = [];
        $notInNfe = [];

        foreach ($orderMeta['products'] as $key => $product) {
            $orderRequestItem = $this->entityManager
                ->getRepository('NatueStockBundle:OrderRequestItem')
                ->findOneBy([
                    'orderRequest' => $orderMeta['order_request'],
                    'zedProduct' => $product['zed_product_id']
                ]);

            if (!$orderRequestItem) {
                $product['request_quantity'] = 0;
                $product['request_diff'] = $product['quantity'];
                $notInRequest[] = $product;

                continue;
            }

            $productIds[] = $product['zed_product_id'];

            $product['request_quantity'] = $orderRequestItem->getQuantity();
            $product['requested_invoice_cost'] = null;
            $product['requested_invoice_cost_flag'] = null;
            $product['requested_invoice_cost_percent'] = null;

            if (!empty($product['zed_product_cost']) || !empty($product['zed_product_average_cost'])) {
                $product['requested_invoice_cost'] =
                    !empty($product['zed_product_cost'])
                        ? $product['zed_product_cost']
                        : $product['zed_product_average_cost'];
                $product['requested_invoice_cost_flag'] =
                    ($product['requested_invoice_cost'] >= $product['unit_price'])
                        ? self::DECREASE_FLAG
                        : self::INCREASE_FLAG;

                $product['requested_invoice_cost_percent'] = $this->calculateCostDiff(
                    $product['requested_invoice_cost'],
                    $product['unit_price']
                );
            }

            $product['request_diff'] = $product['quantity'] - $orderRequestItem->getQuantity();
            $inRequest[] = $product;
        }

        $orderRequestItems = $this->entityManager
            ->getRepository('NatueStockBundle:OrderRequestItem')
            ->findBy([
                'orderRequest' => $orderMeta['order_request']
            ]);

        foreach ($orderRequestItems as $item) {
            if (!in_array($item->getZedProduct()->getId(), $productIds)) {
                $notInNfe[] = $item;
            }
        }

        return [
            'in_request' => $inRequest,
            'not_in_request' => $notInRequest,
            'not_in_nfe' => $notInNfe,
        ];
    }
}
