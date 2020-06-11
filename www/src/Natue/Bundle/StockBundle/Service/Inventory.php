<?php

namespace Natue\Bundle\StockBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Natue\Bundle\StockBundle\Entity\Inventory as InventoryEntity;
use Natue\Bundle\StockBundle\Entity\StockPosition as StockPositionEntity;
use Natue\Bundle\UserBundle\Entity\User as UserEntity;
use Natue\Bundle\StockBundle\Service\InventoryItem as InventoryItemService;

/**
 * Class Inventory
 *
 * @package Natue\Bundle\StockBundle\Service
 */
class Inventory
{
    /** @var \Doctrine\Common\Persistence\ObjectManager */
    private $entityManager;

    /** @var Registry */
    private $doctrine;

    /** @var \Natue\Bundle\StockBundle\Service\InventoryItem */
    private $inventoryItemService;

    /**
     * @param Registry      $doctrine
     * @param InventoryItem $inventoryItemService
     */
    public function __construct(Registry $doctrine, InventoryItemService $inventoryItemService)
    {
        $this->doctrine = $doctrine;
        $this->entityManager = $doctrine->getManager();
        $this->inventoryItemService = $inventoryItemService;
    }

    /**
     * @param StockPositionEntity $stockPosition
     */
    private function lockPosition(StockPositionEntity $stockPosition)
    {
        $stockPosition->setInventory(1);
        $this->entityManager->persist($stockPosition);
        $this->entityManager->flush();
    }

    /**
     * @param StockPositionEntity $stockPosition
     */
    private function unlockPosition(StockPositionEntity $stockPosition)
    {
        $stockPosition->setInventory(0);
        $this->entityManager->persist($stockPosition);
        $this->entityManager->flush();
    }

    /**
     * @param StockPositionEntity $stockPosition
     * @param UserEntity          $user
     * @param bool                $forceStart
     *
     * @return InventoryEntity
     * @throws \Exception
     */
    public function start(StockPositionEntity $stockPosition, UserEntity $user, $forceStart = false)
    {
        if (!$forceStart && $stockPosition->getInventory()) {

            // checking if the user isn't already working on the inventory
            // use case: when user navigated away from the browser window, and is trying to resume the inventory

            $inventoriesByThisUser = $this->doctrine->getRepository('NatueStockBundle:Inventory')
                ->getInventoriesNotFinishedByUserId($user->getId());

            foreach ($inventoriesByThisUser as $inventoryEntity) {

                if ($user->matches($inventoryEntity->getUser())) {
                    return $inventoryEntity;
                }
            }

            throw new \Exception('Inventory for this position already started');
        }

        $inventoryEntity = new InventoryEntity();
        $inventoryEntity
            ->setStockPosition($stockPosition)
            ->setUser($user)
            ->setStartedAt(new \DateTime());

        $this->entityManager->persist($inventoryEntity);
        $this->entityManager->flush();

        $this->lockPosition($stockPosition);

        return $inventoryEntity;
    }

    /**
     * @param InventoryEntity $inventoryEntity
     */
    public function finishInventoriesInPosition(InventoryEntity $inventoryEntity)
    {
        $inventoryEntity->setFinishedAt((new \DateTime()));
        $this->entityManager->persist($inventoryEntity);
        $this->entityManager->flush();

        $this->addMissingInventoryItems($inventoryEntity);
        $this->unlockPosition($inventoryEntity->getStockPosition());
        $this->removeInventoryDuplicates($inventoryEntity);
    }

    /**
     * @param InventoryEntity $inventoryEntity
     */
    public function removeInventoryDuplicates(InventoryEntity $inventoryEntity)
    {
        $inventoriesToRemove = $this->doctrine->getRepository('NatueStockBundle:Inventory')
            ->findBy(
                [
                    'stockPosition' => $inventoryEntity->getStockPosition(),
                    'finishedAt'    => null
                ]
            );

        /** @var \Natue\Bundle\StockBundle\Entity\Inventory $inventoryToRemove */
        foreach ($inventoriesToRemove as $inventoryToRemove) {

            $this->entityManager->remove($inventoryToRemove);
            $this->entityManager->flush();
        }
    }

    /**
     * @param InventoryEntity $inventoryEntity
     */
    public function addMissingInventoryItems(InventoryEntity $inventoryEntity)
    {
        $this->inventoryItemService->addMissingInventoryItems($inventoryEntity);
    }
}
