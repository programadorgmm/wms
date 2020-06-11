<?php

namespace Natue\Bundle\ZedBundle\EventListener;

use Natue\Bundle\CoreBundle\AppEvent;
use Natue\Bundle\ZedBundle\Service\HttpClient;
use Natue\Bundle\StockBundle\Service\StockItemManager;
use Natue\Bundle\ZedBundle\Repository\ZedProductRepository;
use Natue\Bundle\ZedBundle\Repository\ZedProductBarcodeRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Product
 * @package Natue\Bundle\ZedBundle\EventListener
 */
class Product
{
    const STOCK_UPDATED_MESSAGE_TEMPLATE = 'zed product stock updated with data %s';

    /**
     * @var \Natue\Bundle\StockBundle\Service\StockItemManager
     */
    protected $stockItemManager;

    /**
     * @var \Natue\Bundle\ZedBundle\Service\HttpClient
     */
    protected $HTTPClient;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Natue\Bundle\ZedBundle\Repository\ZedProductRepository
     */
    protected $zedProductRepository;

    /**
     * @var \Natue\Bundle\ZedBundle\Repository\ZedProductBarcodeRepository
     */
    protected $zedProductBarcodeRepository;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $doctrine = $container->get('doctrine');

        $this->zedProductRepository = $doctrine->getRepository('NatueZedBundle:ZedProduct');
        $this->zedProductBarcodeRepository = $doctrine->getRepository('NatueZedBundle:ZedProductBarcode');
        $this->stockItemManager = $container->get('natue.stock.item.manager');
        $this->HTTPClient = $container->get('natue.zed.http_client');
        $this->logger = $container->get('logger');
    }

    /**
     * @param AppEvent $appEvent
     */
    public function onStockItemUpdate(AppEvent $appEvent)
    {
        $currentStockData = $this->createZedRequestDataFromAppEvent($appEvent);

        $this->updateProductStockOnZed($currentStockData);
    }

    /**
     * @param array $currentStockData
     */
    protected function updateProductStockOnZed(array $currentStockData)
    {
        $currentStockDataAsJson = json_encode($currentStockData);
        $this->logZedStockUpdate($currentStockDataAsJson);

        $this->HTTPClient->postCurrentStock($currentStockDataAsJson);
    }

    /**
     * @param $currentStockDataAsJson
     */
    protected function logZedStockUpdate($currentStockDataAsJson)
    {
        $this->logger->info(sprintf(
            self::STOCK_UPDATED_MESSAGE_TEMPLATE,
            $currentStockDataAsJson
        ));
    }

    /**
     * @param AppEvent $event
     *
     * @return array
     */
    protected function createZedRequestDataFromAppEvent(AppEvent $event)
    {
        $data = $event->getBusinessData();

        if (!$this->isMultiProductEvent($data)) {
            return [
                $this->prepareProductDataToZed($data)
            ];
        }

        return array_map(function (array $productData) {
            return $this->prepareProductDataToZed($productData);
        }, $data);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function prepareProductDataToZed(array $data)
    {
        $zedProduct = $this->findProductByProductEventData($data);
        $stockQuantity = (int) $this->stockItemManager->getCurrentSellableStockDataByZedProduct($zedProduct);

        if ($stockQuantity < 0) {
            $stockQuantity = 0;
        }

        return [
            'sku' => $zedProduct->getSku(),
            'quantity' => $stockQuantity
        ];
    }

    /**
     * @param array $productData
     *
     * @return \Natue\Bundle\ZedBundle\Entity\ZedProduct
     */
    protected function findProductByProductEventData(array $productData)
    {
        if (!empty($productData['zed_product'])) {
            $zedProduct = $this->zedProductRepository->findOneById($productData['zed_product']);

            return $zedProduct;
        }

        $zedProductBarcode = $this->zedProductBarcodeRepository->findOneByBarcode($productData['barcode']);

        return $zedProductBarcode->getZedProduct();
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    protected function isMultiProductEvent(array $data)
    {
        return empty($data['zed_product']) && empty($data['barcode']);
    }
}
