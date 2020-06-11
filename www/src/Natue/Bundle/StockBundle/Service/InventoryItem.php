<?php

namespace Natue\Bundle\StockBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumInventoryItemStatusType;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\StockBundle\Entity\Inventory as InventoryEntity;
use Natue\Bundle\StockBundle\Entity\InventoryItem as InventoryItemEntity;
use Natue\Bundle\StockBundle\Entity\StockItem as StockItemEntity;
use Natue\Bundle\StockBundle\Form\Model\InventoryItems as InventoryItemsModel;

/**
 * Class InventoryItem
 *
 * @package Natue\Bundle\StockBundle\Service
 */
class InventoryItem
{
    /** @var \Natue\Bundle\StockBundle\Repository\InventoryItemRepository */
    protected $inventoryItemsRepository;

    /** @var \Natue\Bundle\StockBundle\Repository\StockItemRepository */
    protected $stockItemStateMachine;

    /** @var \Doctrine\Common\Persistence\ObjectManager */
    protected $entityManager;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->entityManager = $doctrine->getManager();
        $this->inventoryItemsRepository = $doctrine->getRepository('NatueStockBundle:InventoryItem');
        $this->stockItemRepository = $doctrine->getRepository('NatueStockBundle:StockItem');
    }

    /**
     * @param InventoryEntity     $inventoryEntity
     * @param InventoryItemsModel $inventoryModel
     *
     * @throws \Exception
     */
    public function addInventoryItems(InventoryEntity $inventoryEntity, InventoryItemsModel $inventoryModel)
    {
        $stockItems = $this->stockItemRepository->findBy(
            [
                'status'        => [
                    EnumStockItemStatusType::STATUS_READY,
                    EnumStockItemStatusType::STATUS_ASSIGNED,
                    EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING
                ],
                'stockPosition' => $inventoryEntity->getStockPosition(),
                'barcode'       => $inventoryModel->getBarcode()
            ]
        );

        $countStockItems = count($stockItems);
        if ($countStockItems < $inventoryModel->getQuantity()) {
            throw new \Exception(
                'Barcode: "' . $inventoryModel->getBarcode() . '" ' .
                'Quantity: ' . $inventoryModel->getQuantity() . ' ' .
                'You entered more items then in the system. Total items found: ' . $countStockItems
            );
        }

        // here we have either same quantity or less then in the system
        for ($i = 0; $i < $inventoryModel->getQuantity(); $i++) {

            $inventoryItem = (new InventoryItemEntity())
                ->setStockItem($stockItems[$i])
                ->setInventory($inventoryEntity)
                ->setStatus(EnumInventoryItemStatusType::STATUS_CONFIRMED)
                ->setZedProduct($stockItems[$i]->getZedProduct());

            $this->entityManager->persist($inventoryItem);
            $this->entityManager->flush();
        }
    }

    /**
     * @param int $inventoryId
     * @param int $zedProductId
     */
    public function removeInventoryItems($inventoryId, $zedProductId)
    {
        $itemsToDelete = $this->inventoryItemsRepository->findBy(
            [
                'inventory'  => $inventoryId,
                'zedProduct' => $zedProductId,
            ]
        );

        foreach ($itemsToDelete as $itemToDelete) {
            $this->entityManager->remove($itemToDelete);
            $this->entityManager->flush();
        }
    }

    /**
     * @param InventoryEntity $inventoryEntity
     */
    public function addMissingInventoryItems(InventoryEntity $inventoryEntity)
    {
        $missingStockItems = $this->stockItemRepository->getMissingItemsByInventory($inventoryEntity);

        /** @var StockItemEntity $missingItem */
        foreach ($missingStockItems as $missingItem) {

            $missingInventoryItem = (new InventoryItemEntity())
                ->setStatus(EnumInventoryItemStatusType::STATUS_LOST)
                ->setInventory($inventoryEntity)
                ->setStockItem($missingItem)
                ->setZedProduct($missingItem->getZedProduct());

            $this->entityManager->persist($missingInventoryItem);
            $this->entityManager->flush();
        }
    }

    /**
     * @param InventoryEntity $inventoryEntity
     *
     * @return array
     */
    public function isEqualInventoryItemsWithStockItems(InventoryEntity $inventoryEntity)
    {
        // @todo: extract list of statuses to somewhere into settings
        $stockItems = $this->stockItemRepository->findBy(
            [
                'status'        => [
                    EnumStockItemStatusType::STATUS_READY,
                    EnumStockItemStatusType::STATUS_ASSIGNED,
                    EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING
                ],
                'stockPosition' => $inventoryEntity->getStockPosition()->getId(),
            ]
        );

        $inventoryItems = $this->inventoryItemsRepository
            ->findBy(
                [
                    'inventory' => $inventoryEntity->getId()
                ]
            );

        if (count($stockItems) == count($inventoryItems)) {
            return true;
        }

        return false;
    }
}
