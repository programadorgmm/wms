<?php

namespace Natue\Bundle\StockBundle\Service;

use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;

use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItemReception;
use Natue\Bundle\StockBundle\Entity\StockItem;
use Natue\Bundle\StockBundle\Entity\StockPosition;
use Natue\Bundle\StockBundle\Entity\StockItemStatusHistory;
use Natue\Bundle\StockBundle\Entity\StockItemPositionHistory;
use Natue\Bundle\StockBundle\Repository\StockItemRepository;
use Natue\Bundle\ZedBundle\Entity\ProductCostAverageHistory;
use Natue\Bundle\ZedBundle\Repository\ProductCostAverageHistoryRepository;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;

/**
 * @package Natue\Bundle\StockBundle\Service
 */
class StockItemLogger
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * @var PurchaseOrderItemRepository
     */
    protected $purchaseOrderItemRepository;

    /**
     * @var ProductCostAverageHistoryRepository
     */
    protected $productCostAverageHistoryRepository;

    /**
     * @var string
     */
    protected $sellItemsWithExpirationGreaterThan;

    /**
     * @param Registry        $doctrine
     * @param SecurityContext $securityContext
     * @param string          $sellItemsWithExpirationGreaterThan
     *
     * @return StockItemLogger
     */
    public function __construct(Registry $doctrine, SecurityContext $securityContext, $sellItemsWithExpirationGreaterThan)
    {
        $this->entityManager = $doctrine->getManager();

        // cron doesn't have securityToken defined
        $securityToken = $securityContext->getToken();
        $this->user    = ($securityToken) ? $securityToken->getUser() : null;

        $this->stockItemRepository                 = $doctrine->getRepository('NatueStockBundle:StockItem');
        $this->purchaseOrderItemRepository         = $doctrine->getRepository('NatueStockBundle:PurchaseOrderItem');
        $this->productCostAverageHistoryRepository = $doctrine
            ->getRepository('NatueZedBundle:ProductCostAverageHistory');
        $this->sellItemsWithExpirationGreaterThan = $sellItemsWithExpirationGreaterThan;
    }

    /**
     * Log average Product costs from Purchase Order Reception
     *
     * @param PurchaseOrderItemReception $purchaseOrderItemReception
     *
     * @return void
     */
    public function logPurchaseOrderReceptionCosts(PurchaseOrderItemReception $purchaseOrderItemReception)
    {
        $products = $this->purchaseOrderItemRepository
            ->getProductsQtyAndCostByReception($purchaseOrderItemReception);

        foreach ($products as $product) {
            $zedProduct = $this->entityManager->getRepository('NatueZedBundle:ZedProduct')
                ->findOneById($product['zed_product']);
            if ($zedProduct) {
                $product['zed_product'] = $zedProduct;
                $this->logAverageProductCost($purchaseOrderItemReception, $product);
            }
        }
    }

    /**
     * @param int $newCost
     * @param array $reception
     * @param ZedProduct $product
     */
    public function updateCostAverageFor($newCost, array $receptions, ZedProduct $product)
    {
        foreach ($receptions as $reception) {
            $averageCost = $this->productCostAverageHistoryRepository
                ->getForPurchaseOrderItemReceptionZedProduct($reception, $product);

            $averageCost->updateCostPerItem($newCost);
            $this->entityManager->persist($averageCost);
        }

        $oldestAverageCost = $this->productCostAverageHistoryRepository
            ->getForPurchaseOrderItemReceptionZedProduct($receptions[0], $product);

        $newestProductAverageCostHistory = $this->productCostAverageHistoryRepository
            ->getAllNewestStartingFrom($oldestAverageCost, $product);

        $lastAverageCost = $oldestAverageCost->getCostAverage();

        foreach ($newestProductAverageCostHistory as $productCost) {
            $productCost->updatePreviousCost($lastAverageCost);
            $lastAverageCost = $productCost->getCostAverage();
            $this->entityManager->persist($productCost);
        }

        $this->entityManager->flush();
    }

    /**
     * @param PurchaseOrderItemReception $purchaseOrderItemReception
     * @param array                      $product
     * @return void
     */
    public function logAverageProductCost(PurchaseOrderItemReception $purchaseOrderItemReception, array $product)
    {
        $currentQty = intval(
            $this->stockItemRepository
                ->getCurrentSellableStockDataByZedProduct($product['zed_product'], $this->sellItemsWithExpirationGreaterThan)
        );

        $currentCost = intval(
            $this->productCostAverageHistoryRepository
                ->getAverageCostByZedProduct($product['zed_product'])
        );

        $costHistory = new ProductCostAverageHistory();
        $costHistory->setPreviousCost($currentCost)
          ->setPreviousCount($currentQty)
          ->setAddedCost($product['cost'])
          ->setAddedCount($product['qty'])
          ->setZedProduct($product['zed_product'])
          ->setPurchaseOrderItemReception($purchaseOrderItemReception)
          ->calculateCostAverage();

        $this->entityManager->persist($costHistory);
        $this->entityManager->flush();
    }

    /**
     * @param string $time
     * @return array
     */
    public function getLogs($time = null)
    {
        $dateTime = new \DateTime;

        if ($time) {
            $dateTime = new \DateTime($time);
        }

        return $this->productCostAverageHistoryRepository->getByDateTime($dateTime);
    }

    /**
     * Get QueryBuilder for average cost grid
     *
     * @param \DateTime $dateTime
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderForAverageCostGrid(\DateTime $dateTime)
    {
        if ($dateTime->format('Y-m-d') === (new \DateTime)->format('Y-m-d')) {
            return $this->stockItemRepository->getProductsAverageCostQueryBuilder();
        }

        return $this->productCostAverageHistoryRepository->getByDateTimeQueryBuilder($dateTime);
    }
}
