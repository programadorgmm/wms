<?php

namespace Natue\Bundle\StockBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;

use Natue\Bundle\StockBundle\Entity\Inventory as InventoryEntity;
use Natue\Bundle\StockBundle\Form\Model\Inventory as InventoryModel;
use Natue\Bundle\StockBundle\Form\Type\Inventory as InventoryForm;

use Natue\Bundle\StockBundle\Entity\InventoryItem as InventoryItemEntity;
use Natue\Bundle\StockBundle\Form\Model\InventoryItems as InventoryItemsModel;
use Natue\Bundle\StockBundle\Form\Type\InventoryItems as InventoryItemsForm;

use Doctrine\ORM\EntityManager;
use Natue\Bundle\StockBundle\Entity\StockPosition;

/**
 * Item controller
 *
 * @todo: allow actions ONLY for the same employee OR SUPER ADMIN
 *
 * @Route("/inventory")
 *
 */
class InventoryController extends Controller
{
    /**
     * @Route("/list", name="stock_inventory_list")
     * @Template
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_INVENTORY_LIST")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function listAction(Request $request)
    {
        /** @var \Natue\Bundle\StockBundle\Repository\InventoryRepository $repository */
        $repository = $this->getDoctrine()->getRepository('NatueStockBundle:Inventory');
        $queryBuilder = $repository->getInventoriesForListing();

        /** @var \Natue\Bundle\StockBundle\Grid\PositionGrid $grid */
        $grid = $this->get('pedroteixeira.grid')->createGrid('\Natue\Bundle\StockBundle\Grid\InventoryGrid');
        $grid->setQueryBuilder($queryBuilder);

        if ($grid->isResponseAnswer()) {
            return $grid->render();
        }

        return [
            'grid' => $grid->render()
        ];
    }

    /**
     *
     * @Route("/{inventoryId}/listItems", name="stock_inventory_list_items")
     * @Template
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_INVENTORY_LIST_ITEMS")
     *
     * @param int                                       $inventoryId
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function listItemsAction($inventoryId, Request $request)
    {
        /** @var \Natue\Bundle\StockBundle\Repository\InventoryItemRepository $repository */
        $repository = $this->getDoctrine()->getRepository('NatueStockBundle:InventoryItem');
        $queryBuilder = $repository->getQueryForListAction(
            [
                'inventoryId' => $inventoryId
            ]
        );

        /** @var \Natue\Bundle\StockBundle\Grid\InventoryItemGrid $grid */
        $grid = $this->get('pedroteixeira.grid')->createGrid(
            '\Natue\Bundle\StockBundle\Grid\InventoryItemNoActionGrid'
        );
        $grid->setQueryBuilder($queryBuilder)->setUrl(
            $this->generateUrl(
                'stock_inventory_list_items',
                [
                    'inventoryId' => $inventoryId
                ]
            )
        );

        if ($grid->isResponseAnswer()) {
            return $grid->render();
        }

        $inventoryRepository = $this->getDoctrine()->getRepository('NatueStockBundle:Inventory');
        $inventory = $inventoryRepository->find($inventoryId);

        return [
            'grid'      => $grid->render(),
            'inventory' => $inventory
        ];
    }

    /**
     * @Route("/create", name="stock_inventory_create")
     * @Template
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_INVENTORY_CREATE")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $inventoryFormModel = new InventoryModel();

        $form = $this->createForm(
            new InventoryForm(),
            $inventoryFormModel
        );

        if ($request->isMethod('post')) {
            try {
                $entityManager->getConnection()->beginTransaction();
                $form->submit($request);

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                // checking if stock position is available
                /** @var \Natue\Bundle\StockBundle\Entity\StockPosition $stockPosition */
                $stockPosition = $this->getDoctrine()
                    ->getRepository('NatueStockBundle:StockPosition')
                    ->findOneBy(['name' => $inventoryFormModel->getStockPositionName()]);

                if (!$stockPosition) {
                    throw new \Exception(
                        'Stock Position ' .
                        $inventoryFormModel->getStockPositionName() .
                        ' not found'
                    );
                }

                $inventoryManager = $this->get('natue.stock.inventory.manager');
                $inventoryEntity = $inventoryManager->start($stockPosition, $this->getUser());

                $this->get('session')->getFlashBag()->add('success', 'Created');

                $entityManager->commit();

                return $this->redirect(
                    $this->generateUrl(
                        'stock_inventory_set_items',
                        [
                            'inventoryId' => $inventoryEntity->getId()
                        ]
                    )
                );

            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     *
     * @Route("/{inventoryId}/setItems", name="stock_inventory_set_items")
     * @Template
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_INVENTORY_CREATE")
     *
     * @param int                                       $inventoryId
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function setItemsAction($inventoryId, Request $request)
    {
        $inventoryRepository = $this->getDoctrine()->getRepository('NatueStockBundle:Inventory');
        $inventory = $inventoryRepository->find($inventoryId);

        if (!$inventory) {
            throw $this->createNotFoundException(
                'No inventory found for id ' . $inventoryId
            );
        }

        if ($inventory->getFinishedAt()) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                'This inventory was finished. Please start new inventory'
            );

            return $this->redirect(
                $this->generateUrl(
                    'stock_inventory_list'
                )
            );
        }

        if ($inventory->getUser()->getId() !== $this->getUser()->getId()) {
            $this->get('session')->getFlashBag()->add(
                'danger',
                'Inventory can be continued only by same user'
            );

            return $this->redirect(
                $this->generateUrl(
                    'stock_inventory_list'
                )
            );
        }

        /** @var \Natue\Bundle\StockBundle\Repository\InventoryItemRepository $repository */
        $repository = $this->getDoctrine()->getRepository('NatueStockBundle:InventoryItem');
        $queryBuilder = $repository->getQueryForListAction(['inventoryId' => $inventoryId]);

        /** @var \Natue\Bundle\StockBundle\Grid\PositionGrid $grid */
        $grid = $this->get('pedroteixeira.grid')->createGrid('\Natue\Bundle\StockBundle\Grid\InventoryItemGrid');
        $grid->setQueryBuilder($queryBuilder);
        $grid->setUrl(
            $this->generateUrl(
                'stock_inventory_set_items',
                [
                    'inventoryId' => $inventoryId
                ]
            )
        );

        if ($grid->isResponseAnswer()) {
            return $grid->render();
        }

        $inventoryItemsModel = new InventoryItemsModel();
        $inventoryItemsModel->setPosition($inventoryId);

        $form = $this->createForm(
            new InventoryItemsForm(),
            $inventoryItemsModel
        );

        return [
            'inventoryId' => $inventoryId,
            'form'        => $form->createView(),
            'grid'        => $grid->render()
        ];
    }

    /**
     * @todo: allow only when inventory was never finished
     *
     * @Route("/{inventoryId}/setItemsPost", name="stock_inventory_set_items_post")
     * @Template
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_INVENTORY_CREATE")
     *
     * @param int                                       $inventoryId
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function setItemsPostAction($inventoryId, Request $request)
    {
        if (!$request->isMethod('post')) {
            return $this->redirect(
                $this->generateUrl(
                    'stock_inventory_set_items',
                    [
                        'inventoryId' => $inventoryId
                    ]
                )
            );
        }

        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $inventoryItemsModel = new InventoryItemsModel();
        $inventoryItemsModel->setPosition($inventoryId);

        $form = $this->createForm(
            new InventoryItemsForm(),
            $inventoryItemsModel
        );

        try {
            $entityManager->getConnection()->beginTransaction();
            $form->submit($request);

            if (!$form->isValid()) {
                throw new \Exception('Error on form submission');
            }

            $inventoryEntity = $this->getDoctrine()
                ->getRepository('NatueStockBundle:Inventory')
                ->find($inventoryId);

            if (!$inventoryEntity) {
                throw new \Exception(
                    'Inventory with Id ' .
                    $inventoryId .
                    ' not found'
                );
            }

            if ($inventoryEntity->getFinishedAt()) {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    'Inventory is already finished with id: ' . $inventoryId
                );

                return $this->redirect(
                    $this->generateUrl(
                        'stock_inventory_list'
                    )
                );
            }

            $this->get('natue.stock.inventory.item.manager')->addInventoryItems(
                $inventoryEntity,
                $inventoryItemsModel
            );

            $this->get('session')->getFlashBag()->add(
                'success',
                'Items added'
            );
            $entityManager->getConnection()->commit();

        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl(
                'stock_inventory_set_items',
                [
                    'inventoryId' => $inventoryId
                ]
            )
        );
    }

    /**
     *
     * @Route("/{inventoryId}/{zedProductId}/deleteItems", name="stock_inventory_remove_items")
     * @Template
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_INVENTORY_CREATE")
     *
     * @param int                                       $inventoryId
     * @param int                                       $zedProductId
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function removeItemsAction($inventoryId, $zedProductId, Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        try {
            $entityManager->getConnection()->beginTransaction();

            $inventoryRepository = $this->getDoctrine()->getRepository('NatueStockBundle:Inventory');
            $inventory = $inventoryRepository->find($inventoryId);

            if (!$inventory) {
                throw $this->createNotFoundException(
                    'No inventory found for id ' . $inventoryId
                );
            }

            if ($inventory->getFinishedAt()) {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    'This inventory was finished. Please start new inventory'
                );

                return $this->redirect(
                    $this->generateUrl(
                        'stock_inventory_list'
                    )
                );
            }

            $inventoryManager = $this->get('natue.stock.inventory.item.manager');
            $inventoryManager->removeInventoryItems($inventoryId, $zedProductId);

            $this->get('session')->getFlashBag()->add(
                'success',
                'The inventory items where removed'
            );

            $entityManager->commit();

        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl(
                'stock_inventory_set_items',
                [
                    'inventoryId' => $inventoryId
                ]
            )
        );
    }

    /**
     * @TODO: this action still looks super dirty. Cleanup
     *
     * @Route("/{inventoryId}/initControlInventory", name="stock_inventory_init_control_inventory")
     * @Template
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_INVENTORY_CREATE")
     *
     * @param int                                       $inventoryId
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function initControlInventoryAction($inventoryId, Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $inventoryEntity = $this->getDoctrine()->getRepository('NatueStockBundle:Inventory')->find($inventoryId);

        try {
            $entityManager->getConnection()->beginTransaction();

            if (!$inventoryEntity) {
                throw $this->createNotFoundException(
                    'Inventory is not found for id: ' . $inventoryId
                );
            }

            $inventoryItemManager = $this->get('natue.stock.inventory.item.manager');

            if ($inventoryItemManager->isEqualInventoryItemsWithStockItems($inventoryEntity)) {
                $this->finishInventoriesInPosition($inventoryEntity);

                $entityManager->getConnection()->commit();

                return $this->redirect(
                    $this->generateUrl(
                        'stock_inventory_list_items',
                        [
                            'inventoryId' => $inventoryId
                        ]
                    )
                );
            }

            // ok, we do NOT have a success.
            // so we ask to enter second time.

            /** @var \Natue\Bundle\StockBundle\Repository\InventoryRepository $repository */
            $repository = $this->getDoctrine()->getRepository('NatueStockBundle:Inventory');
            $inventoriesForStockPosition = $repository->getGetInventoriesNotFinished(
                $inventoryEntity->getStockPosition()
            );

            if (count($inventoriesForStockPosition) <= 1) {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Please do inventory again on position: ' . $inventoryEntity->getStockPosition()->getName()
                );

                $inventoryManager = $this->get('natue.stock.inventory.manager');
                $inventoryEntity = $inventoryManager->start(
                    $inventoryEntity->getStockPosition(),
                    $this->getUser(),
                    true
                );

                $entityManager->commit();

                return $this->redirect(
                    $this->generateUrl(
                        'stock_inventory_set_items',
                        [
                            'inventoryId' => $inventoryEntity->getId()
                        ]
                    )
                );
            }

            // At this point we have several inventories. They have to match in order to finish inventory
            // ----------------------------------------------------------

            $inventoryLast = array_pop($inventoriesForStockPosition);
            $inventoryPrevious = array_pop($inventoriesForStockPosition);

            $repositoryItems = $this->getDoctrine()->getRepository('NatueStockBundle:InventoryItem');

            $inventoryLastItems = $repositoryItems->getInventoryItemsGrouped(
                [
                    'inventoryId' => $inventoryLast['id']
                ]
            );

            $inventoryPreviousItems = $repositoryItems->getInventoryItemsGrouped(
                [
                    'inventoryId' => $inventoryPrevious['id']
                ]
            );

            // has to be a transfer object
            $comparison = [
                'success'                    => true,
                'itemsNotMatchingQuantities' => [],
                'itemsMissing'               => []
            ];

            foreach ($inventoryLastItems as $inventoryItemLast) {
                $missing = true;
                foreach ($inventoryPreviousItems as $inventoryItemPrevious) {
                    if ($inventoryItemPrevious['barcode'] == $inventoryItemPrevious['barcode']) {
                        $missing = false;
                        $quantityDiff = $inventoryItemLast['qty'] - $inventoryItemPrevious['qty'];

                        if ($quantityDiff != 0) {
                            $comparison['success'] = false;
                            $comparison['itemsNotMatchingQuantities'][] = [
                                'name'     => $inventoryItemLast['productName'],
                                'barcode'  => $inventoryItemLast['barcode'],
                                'quantity' => $quantityDiff
                            ];
                        }
                    }
                }

                if ($missing) {
                    $comparison['success'] = false;
                    $comparison['itemsMissing'][] = $inventoryItemLast;
                }
            }

            if ($comparison['success'] === true) {
                $this->finishInventoriesInPosition($inventoryEntity);

                $entityManager->getConnection()->commit();

                return $this->redirect(
                    $this->generateUrl(
                        'stock_inventory_list_items',
                        [
                            'inventoryId' => $inventoryId
                        ]
                    )
                );
            }

            // here we have 2 inventories which are not matching. Asking to enter again

            foreach ($comparison['itemsMissing'] as $itemMissing) {
                $this->get('session')->getFlashBag()->add(
                    'danger',
                    'Quantity for product "' . $itemMissing['productName'] . '" did not match'
                );
            }

            $this->get('session')->getFlashBag()->add(
                'danger',
                'Please enter inventory once more'
            );

            $inventoryManager = $this->get('natue.stock.inventory.manager');
            $inventoryEntity = $inventoryManager->start($inventoryEntity->getStockPosition(), $this->getUser(), true);

        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
        }

        return $this->redirect(
            $this->generateUrl(
                'stock_inventory_set_items',
                [
                    'inventoryId' => $inventoryEntity->getId()
                ]
            )
        );
    }

    /**
     * Closing passed inventory. Also closing other inventories on this position
     *
     * @param InventoryEntity $inventoryEntity
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function finishInventoriesInPosition(InventoryEntity $inventoryEntity)
    {
        $inventoryManager = $this->get('natue.stock.inventory.manager');
        $inventoryManager->finishInventoriesInPosition($inventoryEntity);

        $this->get('session')->getFlashBag()->add(
            'success',
            'Inventory succeeded! Position: ' . $inventoryEntity->getStockPosition()->getName()
        );
    }
}
