<?php

namespace Natue\Bundle\StockBundle\Service;

use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItem;
use Natue\Bundle\StockBundle\Entity\StockPosition;
use Natue\Bundle\StockBundle\Entity\StockItem;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItemReception;
use Natue\Bundle\StockBundle\Repository\StockItemRepository;
use Natue\Bundle\StockBundle\Repository\PurchaseOrderRepository;
use Natue\Bundle\StockBundle\Repository\PurchaseOrderItemRepository;
use Natue\Bundle\StockBundle\Repository\PurchaseOrderItemReceptionRepository;
use Natue\Bundle\StockBundle\StateMachine\PurchaseOrderItem as PurchaseOrderItemStateMachine;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumPurchaseOrderItemStatusType as PurchaseOrderItemStateTransition;
use Natue\Bundle\StockBundle\Service\StockItemLogger;
use Natue\Bundle\CoreBundle\Service\BatchProcessing;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;

/**
 * PurchaseOrderReception
 */
class PurchaseOrderReception
{
    /**
     * @var Registry
     */
    protected $doctrine;

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
     * @var EntityRepository
     */
    protected $stockPositionRepository;

    /**
     * @var PurchaseOrderRepository
     */
    protected $purchaseOrderRepository;

    /**
     * @var PurchaseOrderItemRepository
     */
    protected $purchaseOrderItemRepository;

    /**
     * @var PurchaseOrderItemReceptionRepository
     */
    protected $purchaseOrderItemReceptionRepository;

    /**
     * @var PurchaseOrderItemStateMachine
     */
    protected $purchaseOrderItemStateMachine;

    /**
     * @var StockItemManager
     */
    protected $stockItemManager;

    /**
     * @var BatchProcessing
     */
    protected $batchProcessing;

    /**
     * @var StockItemLogger
     */
    protected $stockItemLogger;

    /**
     * @param Registry                      $doctrine
     * @param SecurityContext               $securityContext
     * @param PurchaseOrderItemStateMachine $purchaseOrderItemStateMachine
     * @param StockItemManager              $stockItemManager
     * @param BatchProcessing               $batchProcessing
     * @param StockItemLogger               $stockItemLogger
     *
     * @return PurchaseOrderReception
     */
    public function __construct(
        Registry $doctrine,
        SecurityContext $securityContext,
        PurchaseOrderItemStateMachine $purchaseOrderItemStateMachine,
        StockItemManager $stockItemManager,
        BatchProcessing $batchProcessing,
        StockItemLogger $stockItemLogger
    ) {
        $this->doctrine      = $doctrine;
        $this->entityManager = $doctrine->getManager();

        $this->user = $securityContext->getToken()->getUser();

        $this->stockItemManager              = $stockItemManager;
        $this->stockItemLogger               = $stockItemLogger;
        $this->batchProcessing               = $batchProcessing;
        $this->purchaseOrderItemStateMachine = $purchaseOrderItemStateMachine;

        $this->stockItemRepository                  = $doctrine->getRepository('NatueStockBundle:StockItem');
        $this->stockPositionRepository              = $doctrine->getRepository('NatueStockBundle:StockPosition');
        $this->purchaseOrderRepository              = $doctrine->getRepository('NatueStockBundle:PurchaseOrder');
        $this->purchaseOrderItemRepository          = $doctrine->getRepository('NatueStockBundle:PurchaseOrderItem');
        $this->purchaseOrderItemReceptionRepository = $doctrine
            ->getRepository('NatueStockBundle:PurchaseOrderItemReception');
    }

    /**
     * @param int $newCost
     * @param PurchaseOrder $purchaseOrder
     * @param ZedProduct $zedProduct
     */
    public function updatePurchaseOrderCostAverage(
        array $purchaseOrderItems,
        $newCost,
        PurchaseOrder $purchaseOrder,
        ZedProduct $zedProduct
    ) {
        $purchaseOrderItemsReception = $this->purchaseOrderItemReceptionRepository
            ->getReceivedPurchaseOrderItemReception($purchaseOrder, $zedProduct);

        $this->stockItemLogger->updateCostAverageFor($newCost, $purchaseOrderItemsReception, $zedProduct);

        foreach ($purchaseOrderItems as $purchaseOrderItem) {
            $purchaseOrderItem->setCost($newCost);
            $this->entityManager->persist($purchaseOrderItem);
        }

        $this->entityManager->flush();
    }

    /**
     * @param $purchaseOrderId
     * @throws \Exception
     * @return void
     */
    public function confirmVolumeDistribution($purchaseOrderId)
    {
        /* @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->purchaseOrderRepository->findOneById($purchaseOrderId);

        if (!$purchaseOrder) {
            throw new \Exception('Purchase Order not found');
        }

        $purchaseOrderItems = new ArrayCollection(
            $this->purchaseOrderItemRepository->findReceivingItemsByPurchaseOrderAndUser(
                $purchaseOrder,
                $this->user
            )
        );

        $purchaseOrderItemReception = $this->purchaseOrderItemReceptionRepository
            ->getInProgressUserPurchaseOrderItemReception(
                $purchaseOrder,
                $this->user
            );

        $this->stockItemLogger->logPurchaseOrderReceptionCosts($purchaseOrderItemReception);

        $this->stockItemManager->updateItemsStatusToReady($purchaseOrder);

        $this->purchaseOrderItemStateMachine->applyTransitionOnCollection(
            PurchaseOrderItemStateTransition::STATUS_RECEIVED,
            $purchaseOrderItems
        );

        $this->updatePurchaseOrderVolumesReceived($purchaseOrder, $purchaseOrderItemReception);
    }

    /**
     * Put PurchaseOrderItems at Position
     *
     * @param PurchaseOrder $purchaseOrder
     * @param string        $barcode
     * @param \DateTime     $dateExpiration
     * @param int           $quantity
     * @param StockPosition $stockPosition
     *
     * @throws \Exception
     */
    public function putPurchaseOrderItemsAtPosition(
        PurchaseOrder $purchaseOrder,
        $barcode,
        \DateTime $dateExpiration,
        $quantity,
        StockPosition $stockPosition
    ) {
        $validateCount = $this->stockItemRepository->countBarcodeAndDateExpirationInPosition(
            $barcode,
            $dateExpiration,
            $stockPosition
        );

        if ($validateCount > 0) {
            throw new \Exception('Position already have the same product with different expiration date or barcode');
        }

        $purchaseOrderItems = $this->purchaseOrderItemRepository
            ->findIncomingItemsByBarcode(
                $purchaseOrder,
                $barcode,
                [
                    'limit'     => $quantity,
                ]
            );

        if ($quantity > count($purchaseOrderItems)) {
            throw new \Exception('Not enough Purchase Order Items found');
        }

        $purchaseOrderItemReception = $this->purchaseOrderItemReceptionRepository
            ->getInProgressUserPurchaseOrderItemReception(
                $purchaseOrder,
                $this->user
            );

        if (!$purchaseOrderItemReception) {
            throw new \Exception('User\'s Reception not found');
        }

        $this->addStockItemsFromPurchaseOrderByBarcode(
            $purchaseOrder,
            $barcode,
            $dateExpiration,
            $stockPosition,
            $quantity
        );

        $this->updatePurchaseOrderItemsReception($purchaseOrderItems, $purchaseOrderItemReception);
    }

    /**
     * @param int $purchaseOrderId
     *
     * @throws \Exception
     */
    public function tryCancelVolumeDistribution($purchaseOrderId)
    {
        /* @var PurchaseOrder $purchaseOrder */
        $purchaseOrder = $this->purchaseOrderRepository->findOneById($purchaseOrderId);

        if (!$purchaseOrder) {
            throw new \Exception('Purchase Order not found');
        }

        $purchaseOrderItems = new ArrayCollection(
            $this->purchaseOrderItemRepository->findReceivingItemsByPurchaseOrderAndUser(
                $purchaseOrder,
                $this->user
            )
        );

        $this->removeStockItemsFromOrderItems($purchaseOrderItems);

        $this->purchaseOrderItemStateMachine->applyTransitionOnCollection(
            PurchaseOrderItemStateTransition::STATUS_INCOMING,
            $purchaseOrderItems
        );

        $this->unsignPurchaseOrderItemsFromReception($purchaseOrderItems);

        $this->removePurchaseOrderItemReception($purchaseOrder);
    }

    /**
     * Prepare data for distribution table
     *
     * @param PurchaseOrder $purchaseOrder
     *
     * @return array
     */
    public function getDataForDistributionTable(PurchaseOrder $purchaseOrder)
    {
        $productsList = $this->purchaseOrderItemRepository->getOrderedProductsWithQuantity($purchaseOrder);

        foreach ($productsList as $key => $product) {
            $distributedItemsCount = $this->purchaseOrderItemReceptionRepository
                ->getCurrentDistributedItemsCount(
                    $purchaseOrder,
                    $this->user,
                    $product['zedProductId']
                );

            $productsList[$key]['currentDistribution'] = $distributedItemsCount;
        }

        return $productsList;
    }

    /**
     * @param $id
     *
     * @return array
     */
    public function getPurchaseOrderDetails($id)
    {
        return $this->purchaseOrderRepository->findWithSupplier($id);
    }

    /**
     * Get QueryBuilder for orders grid
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderForOrdersGrid()
    {
        return $this->purchaseOrderRepository->createQueryBuilderForOrdersGrid();
    }

    /**
     * Get QueryBuilder for order items grid
     *
     * @param PurchaseOrder $purchaseOrder
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderForOrderItemsGrid(PurchaseOrder $purchaseOrder)
    {
        return $this->purchaseOrderItemRepository->getQueryForListAction($purchaseOrder);
    }

    /**
     * Update PurchaseOrderItems status
     *
     * @param ArrayCollection $purchaseOrderItems PurchaseOrderItem collection
     *
     * @return mixed
     */
    private function unsignPurchaseOrderItemsFromReception(ArrayCollection $purchaseOrderItems)
    {
        $purchaseOrderItemIds = $purchaseOrderItems->map(
            function ($orderItem) {
                return $orderItem->getId();
            }
        )->toArray();

        return $this->purchaseOrderItemRepository->
            unsignPurchaseOrderItemsFromReception($purchaseOrderItemIds);
    }

    /**
     * Update PurchaseOrder Volumes Received
     *
     * @param PurchaseOrder              $purchaseOrder
     * @param PurchaseOrderItemReception $purchaseOrderItemReception
     *
     * @return void
     */
    private function updatePurchaseOrderVolumesReceived(
        PurchaseOrder $purchaseOrder,
        PurchaseOrderItemReception $purchaseOrderItemReception
    ) {
        $this->purchaseOrderRepository->increasePurchaseOrderVolumesReceived(
            $purchaseOrder,
            $purchaseOrderItemReception
        );
    }

    /**
     * Remove StockItems from OrderItems
     *
     * @param ArrayCollection $purchaseOrderItems
     *
     * @return void
     */
    private function removeStockItemsFromOrderItems(ArrayCollection $purchaseOrderItems)
    {
        $purchaseOrderItemIds = $purchaseOrderItems->map(
            function ($orderItem) {
                return $orderItem->getId();
            }
        )->toArray();

        $connection = $this->entityManager->getConnection();
        $statement = $connection->executeQuery(
            "DELETE FROM stock_item WHERE purchase_order_item IN (?)",
            array($purchaseOrderItemIds),
            array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
        );

        $this->entityManager->flush();
    }

    /**
     * Remove PurchaseOrderItemReception
     *
     * @param PurchaseOrder $purchaseOrder
     *
     * @return void
     */
    private function removePurchaseOrderItemReception(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrderItemReception = $this->purchaseOrderItemReceptionRepository
            ->getInProgressUserPurchaseOrderItemReception(
                $purchaseOrder,
                $this->user
            );

        $this->entityManager->remove($purchaseOrderItemReception);
        $this->entityManager->flush();
    }

    /**
     * @param array                      $purchaseOrderItems
     * @param PurchaseOrderItemReception $purchaseOrderItemReception
     *
     * @return void
     */
    private function updatePurchaseOrderItemsReception(
        array $purchaseOrderItems,
        PurchaseOrderItemReception $purchaseOrderItemReception
    ) {
        $purchaseOrderItemIds = array_map(function ($orderItem) {
            return $orderItem->getId();
        }, $purchaseOrderItems);

        $sql = "
            UPDATE purchase_order_item
            SET status = ?,
              purchase_order_item_reception = ?,
              updated_at = now()
            WHERE id IN (?)";

        $connection = $this->entityManager->getConnection();
        $statement = $connection->executeQuery(
            $sql,
            array(
                PurchaseOrderItemStateTransition::STATUS_RECEIVING,
                $purchaseOrderItemReception->getId(),
                $purchaseOrderItemIds
            ),
            array(
                \PDO::PARAM_STR,
                \PDO::PARAM_INT,
                \Doctrine\DBAL\Connection::PARAM_INT_ARRAY
            )
        );

        $this->entityManager->flush();
    }

    /**
     * Add StockItems from PurchaseOrder by Barcode
     *
     * @param PurchaseOrder $purchaseOrder
     * @param string        $barcode
     * @param \DateTime     $dateExpiration
     * @param StockPosition $stockPosition
     * @param int           $quantity
     *
     * @return void
     */
    private function addStockItemsFromPurchaseOrderByBarcode(
        PurchaseOrder $purchaseOrder,
        $barcode,
        \DateTime $dateExpiration,
        StockPosition $stockPosition,
        $quantity
    ) {
        $sql = "
            INSERT INTO stock_item (date_expiration, barcode, created_at,
              stock_position, zed_product, purchase_order_item, user)
            SELECT :expiration_date, zpb.barcode, now(),
              :stock_position, poi.zed_product, poi.id, :user
            FROM purchase_order_item poi
            INNER JOIN zed_product_barcode zpb ON poi.zed_product = zpb.zed_product
            WHERE poi.purchase_order = :purchase_order
              AND poi.status = :status
              AND zpb.barcode = :barcode
            LIMIT $quantity";

        $connection = $this->entityManager->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindValue('expiration_date', $dateExpiration, 'datetime');
        $statement->bindValue('stock_position', $stockPosition->getId());
        $statement->bindValue('user', $this->user->getId());
        $statement->bindValue('purchase_order', $purchaseOrder->getId());
        $statement->bindValue('status', PurchaseOrderItemStateTransition::STATUS_INCOMING);
        $statement->bindValue('barcode', $barcode);
        $statement->execute();

        $this->entityManager->flush();
    }
}
