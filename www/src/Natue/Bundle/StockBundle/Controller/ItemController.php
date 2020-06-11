<?php

namespace Natue\Bundle\StockBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Natue\Bundle\StockBundle\Grid\Datatable\ItemGrid;
use Natue\Bundle\StockBundle\Grid\Datatable\ItemGridBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use JMS\SecurityExtraBundle\Annotation\Secure;

use Natue\Bundle\StockBundle\Entity\StockPosition;
use Natue\Bundle\StockBundle\Form\Model\StockItem as StockItemModel;
use Natue\Bundle\StockBundle\Form\Type\StockItem as StockItemForm;
use Natue\Bundle\StockBundle\Form\Model\StockItemListFilters as StockItemListFiltersModel;
use Natue\Bundle\StockBundle\Form\Type\StockItemListFilters as StockItemListFiltersForm;
use Natue\Bundle\StockBundle\Form\Model\StockItemMove as StockItemMoveModel;
use Natue\Bundle\StockBundle\Form\Model\StockItemMoveFromPosition as StockItemMoveModelFromPosition;
use Natue\Bundle\StockBundle\Form\Type\StockItemMoveFromItem as StockItemMoveFormFromItem;
use Natue\Bundle\StockBundle\Form\Type\StockItemMove as StockItemMoveForm;
use Natue\Bundle\StockBundle\Form\Type\StockItemMoveFromPosition as StockItemMoveFormFromPosition;
use Natue\Bundle\StockBundle\Repository\StockItemRepository;
use Natue\Bundle\StockBundle\Service\StockItemManager;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Item controller
 *
 * @Route("/item")
 */
class ItemController extends Controller
{
    const STATUS_INDEX = 5;
    const SINGLE_PRODUCT_SKU_LENGTH = 10;

    /**
     * List action
     *
     * @Route("/list", name="stock_item_list", options={"expose"=true})
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_ITEM_READ")
     *
     * @param Request $request
     *
     * @throws \Exception
     * @return array
     */
    public function listAction(Request $request)
    {
        $datatable = $this->createStockItemGrid();

        $form = $this->createForm(new StockItemMoveFormFromItem());


        return [
            'datatable' => $datatable,
            'form'      => $form->createView(),
            'clear'     => $this->get('translator')->trans('Clear')
        ];
    }

    /**
     * @Route("/results", name="stock_item_results", options={"expose"=true})
     */
    public function indexResultsAction(Request $request)
    {
        $datatable = $this->createStockItemGrid();

        /** @var \Natue\Bundle\CoreBundle\Datatable\Query\DatatableQuery $query */
        $query = $this->get('natue_datatables.query')->getQueryFrom($datatable);

        $query->addWhereAll(function (QueryBuilder $queryBuilder) use ($request) {
            $queryBuilder
                ->addGroupBy('zedProduct.sku')
                ->addGroupBy('stockPosition.id')
                ->addGroupBy('stock_item.status')
                ->addGroupBy('stock_item.barcode')
                ->addGroupBy('stock_item.dateExpiration')
                ->andWhere('stockPosition.enabled = :enabled')
                ->setParameter('enabled', true)
            ;

            if (!$request->query->get('columns')[self::STATUS_INDEX]['search']['value']) {
                $queryBuilder->andWhere('stock_item.status != :status')
                             ->setParameter('status', EnumStockItemStatusType::STATUS_SOLD);
            }

            $queryBuilder->andWhere(
                $queryBuilder->expr()->isNotNull('stockPosition.id')
            );

            $queryBuilder->andWhere('TRIM(LENGTH(zedProduct.sku)) = :singleProductSkuLength')
                         ->setParameter('singleProductSkuLength', self::SINGLE_PRODUCT_SKU_LENGTH);

        });

        return $query->getResponse();
    }

    /**
     * Update action
     *
     * @Route("/{sku}/{positionId}/{status}/{barcode}/{dateExpiration}/update", name="stock_item_update", options={"expose"=true})
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_PURCHASE_ORDER_ITEM_UPDATE")
     *
     * @param string $sku
     * @param int $positionId
     * @param string $status
     * @param string $barcode
     * @param string $dateExpiration
     * @param Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Exception
     * @return array
     */
    public function updateAction($sku, $positionId, $status, $barcode, $dateExpiration, Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        /** @var StockPosition $stockPosition */
        $stockPosition = $entityManager->getRepository('NatueStockBundle:StockPosition')
                                       ->findOneById($positionId);
        if (!$stockPosition) {
            throw $this->createNotFoundException('Position not found');
        }

        /** @var ZedProduct $zedProduct */
        $zedProduct = $entityManager->getRepository('NatueZedBundle:ZedProduct')
                                    ->findOneBySku($sku);
        if (!$zedProduct) {
            throw $this->createNotFoundException('Product not found');
        }

        $entityManager->getConnection()->beginTransaction();

        /** @var StockItemRepository $stockItemRepository */
        $stockItemRepository = $this->getDoctrine()->getRepository('NatueStockBundle:StockItem');

        if (!filter_var($dateExpiration, FILTER_VALIDATE_INT)) {
            $dateExpiration = new \DateTime($dateExpiration);
        } else {
            $timestamp = $dateExpiration;
            $dateExpiration = new \DateTime();
            $dateExpiration->setTimestamp($timestamp);
        }

        $stockItemList = $stockItemRepository->findForUpdateAction(
            $zedProduct,
            $stockPosition,
            $status,
            $dateExpiration,
            $barcode
        );

        if (!$stockItemList) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            throw $this->createNotFoundException('Stock item not found');
        }

        $stockItemFormModel = new StockItemModel();
        $stockItemFormModel->setBarcode($stockItemList[0]->getBarcode())
                           ->setDateExpiration($stockItemList[0]->getDateExpiration());

        $form = $this->createForm(
            new StockItemForm(),
            $stockItemFormModel
        );

        if ($request->isMethod('post')) {
            try {
                $form->submit($request);

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                /** @var \Natue\Bundle\StockBundle\Service\BatchProcessingStockItem $batchProcessing */
                $batchProcessing = $this->get('natue.stock.item.batchprocessing');

                $batchProcessing->bulkUpdate(
                    $zedProduct,
                    $stockPosition,
                    $status,
                    $dateExpiration,
                    $barcode,
                    $stockItemFormModel->getDateExpiration(),
                    $stockItemFormModel->getBarcode()
                );

                $this->get('session')->getFlashBag()->add('success', 'Updated');

                $entityManager->getConnection()->commit();

                return $this->redirect(
                    $this->generateUrl(
                        'stock_item_update',
                        [
                            'sku'            => $sku,
                            'positionId'     => $positionId,
                            'status'         => $status,
                            'barcode'        => $stockItemFormModel->getBarcode(),
                            'dateExpiration' => $stockItemFormModel->getDateExpiration()->format('Y-m-d')
                        ]
                    )
                );
            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        } else {
            $entityManager->getConnection()->commit();
        }

        return [
            'sku'            => $sku,
            'positionId'     => $positionId,
            'status'         => $status,
            'barcode'        => $barcode,
            'dateExpiration' => $dateExpiration,
            'form'           => $form->createView()
        ];
    }

    /**
     * MoveFromPositionAction action
     *
     * @Route("/move-from-position", name="stock_item_move_from_position")
     * @Template()
     * @Secure(roles="ROLE_SUPER_ADMIN,ROLE_STOCK_ITEM_MOVE_FROM_POSITION")
     *
     * @param Request $request
     *
     * @throws \Exception
     * @return array
     */
    public function moveFromPositionAction(Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $stockItemMoveModelFromPosition = new StockItemMoveModelFromPosition();
        $form = $this->createForm(
            new StockItemMoveFormFromPosition(),
            $stockItemMoveModelFromPosition
        );

        if ($request->isMethod('post')) {
            $form->submit($request);

            try {
                $entityManager->getConnection()->beginTransaction();
                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                $oldStockPositionId = $stockItemMoveModelFromPosition->getOldStockPositionId();
                /** @var StockPosition $oldStockPosition */
                $oldStockPosition = $this->getDoctrine()
                                         ->getRepository('NatueStockBundle:StockPosition')
                                         ->findOneById($oldStockPositionId);
                if (!$oldStockPosition) {
                    throw new \Exception('Position "from" not found');
                }

                $newStockPositionId = $stockItemMoveModelFromPosition->getNewStockPositionId();
                /** @var StockPosition $newStockPosition */
                $newStockPosition = $this->getDoctrine()
                                         ->getRepository('NatueStockBundle:StockPosition')
                                         ->findOneById($newStockPositionId);
                if (!$newStockPosition) {
                    throw new \Exception('Position "to" not found');
                }

                if ($oldStockPositionId == $newStockPositionId) {
                    throw new \Exception('Position "from" and Position "to" are the same');
                }

                $stockItemList = $this->getDoctrine()
                                      ->getRepository('NatueStockBundle:StockItem')
                                      ->findByFilters(['stockPosition' => $oldStockPosition]);
                if (!$stockItemList) {
                    throw new \Exception('No stock item found');
                }

                /** @var StockItemManager $stockItemManager */
                $stockItemManager = $this->get('natue.stock.item.manager');

                foreach ($stockItemList as $stockItem) {
                    $stockItemManager->changePosition($stockItem, $newStockPosition);
                }

                $this->get('session')->getFlashBag()->add('success', 'Items were successfully moved');

                $entityManager->getConnection()->commit();

                return $this->redirect($this->generateUrl('stock_item_move_from_position'));

            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    public function getSessionCheckbox()
    {
        $session = $this->get('session');
        return [
            'from_position' => $session->get('StockMoveItemFormFromPositionChecked'),
            'to_position_check' => $session->get('StockMoveItemFormToPositionChecked'),
            'quantity_check' => $session->get('StockMoveItemFormQuantityChecked')
        ];
    }

    public function createStockItemMoveForm(StockItemMoveModel $stockItemMoveModel, array $stockItemMoveData)
    {
        $stockItemMoveModel->setOldStockPositionCheck((bool) $stockItemMoveData['from_position']);
        $stockItemMoveModel->setOldStockPositionId($stockItemMoveData['from_position']);
        
        $stockItemMoveModel->setNewStockPositionCheck((bool) $stockItemMoveData['to_position_check']);
        $stockItemMoveModel->setNewStockPositionId($stockItemMoveData['to_position_check']);
        
        $stockItemMoveModel->setQuantityCheck((bool) $stockItemMoveData['quantity_check']);
        $stockItemMoveModel->setQuantity($stockItemMoveData['quantity_check']);

        return $this->createForm(
            new StockItemMoveForm(),
            $stockItemMoveModel
        );
    }

    public function checkSessionCheckbox(StockItemMoveModel $stockItemMoveModel)
    {
        if($stockItemMoveModel->getOldStockPositionCheck()){
            $this->get('session')->set('StockMoveItemFormFromPositionChecked', $stockItemMoveModel->getOldStockPositionId());
        } else {
            $this->get('session')->remove('StockMoveItemFormFromPositionChecked');
        }

        if($stockItemMoveModel->getNewStockPositionCheck()){
            $this->get('session')->set('StockMoveItemFormToPositionChecked', $stockItemMoveModel->getNewStockPositionId());
        }else{
            $this->get('session')->remove('StockMoveItemFormToPositionChecked');
        }

        if($stockItemMoveModel->getQuantityCheck()){
            $this->get('session')->set('StockMoveItemFormQuantityChecked', $stockItemMoveModel->getQuantity());
        }else{
            $this->get('session')->remove('StockMoveItemFormQuantityChecked');
        }
    }

    /**
     * Move action
     *
     * @TODO -> The WMS will validate if the stock items are already in a picking list;
     * @TODO -> The WMS will validate the position for same SKU with different expiration date or barcode;
     * @TODO -> The WMS will check if the stock items are allocated to any order, if yes, adjust the order;
     *
     * @Route("/move", name="stock_item_move")
     * @Template()
     * @Secure(roles="ROLE_ADMIN,ROLE_STOCK_ITEM_MOVE")
     *
     * @param Request $request
     *
     * @throws \Exception
     * @return array
     */
    public function moveAction(Request $request)
    {
        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        //Verifica  os  dados da session e salvar no StockItemMoveModel.
        $stockItemMoveModel = new StockItemMoveModel();
        $form = $this->createStockItemMoveForm($stockItemMoveModel, $this->getSessionCheckbox());
        if ($request->isMethod('post')) {
            $form->submit($request);
            $this->checkSessionCheckbox($stockItemMoveModel);
            try {
                $entityManager->getConnection()->beginTransaction();
                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                $oldStockPositionId = $stockItemMoveModel->getOldStockPositionId();
                /** @var StockPosition $oldStockPosition */
                $oldStockPosition = $this->getDoctrine()
                                         ->getRepository('NatueStockBundle:StockPosition')
                                         ->findOneById($oldStockPositionId);
                if (!$oldStockPosition) {
                    throw new \Exception('Position "from" not found');
                }

                $newStockPositionId = $stockItemMoveModel->getNewStockPositionId();
                /** @var StockPosition $newStockPosition */
                $newStockPosition = $this->getDoctrine()
                                         ->getRepository('NatueStockBundle:StockPosition')
                                         ->findOneById($newStockPositionId);
                if (!$newStockPosition) {
                    throw new \Exception('Position "to" not found');
                }

                if ($oldStockPositionId == $newStockPositionId) {
                    throw new \Exception('Position "from" and Position "to" are the same');
                }

                $quantity = $stockItemMoveModel->getQuantity();
                $barcode = $stockItemMoveModel->getBarcode();

                $stockItemList = $this->getDoctrine()
                                      ->getRepository('NatueStockBundle:StockItem')
                                      ->findByFilters([
                                                          'barcode'       => $barcode,
                                                          'stockPosition' => $oldStockPosition,
                                                          'limit'         => $quantity,
                                                          'status'        => EnumStockItemStatusType::STATUS_READY
                                                      ]);

                if (!$stockItemList) {
                    throw new \Exception(
                        'No stock item found from barcode:' . $barcode . ' at stock position:'
                        . $oldStockPosition->getName()
                    );
                } elseif ($quantity > count($stockItemList)) {
                    throw new \Exception(
                        'Quantity is greater than the number of stock item with this barcode and that Stock Position'
                    );
                }

                /** @var StockItemManager $stockItemManager */
                $stockItemManager = $this->get('natue.stock.item.manager');

                foreach ($stockItemList as $stockItem) {
                    $stockItemManager->changePosition($stockItem, $newStockPosition);
                }

                $this->get('session')->getFlashBag()->add('success', 'Items were successfully moved');

                $entityManager->getConnection()->commit();

                return $this->redirect($this->generateUrl('stock_item_move'));

            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();
                $this->get('session')->getFlashBag()->add('danger', $e->getMessage());
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * MoveFromItems action
     *
     * @Route("/move-from-items", name="stock_item_move_from_items")
     * @Template()
     * @Secure(roles="ROLE_ADMIN, ROLE_STOCK_ITEM_MOVE")
     *
     * @param Request $request
     *
     * @throws \Exception
     * @return array
     */
    public function moveFromItemsAction(Request $request)
    {
        $params = $request->request->get('natue_stockbundle_position_move_from_item');

        /* @var \Doctrine\ORM\EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        $form = $this->createForm(new StockItemMoveFormFromItem());
        if ($request->isMethod('POST')) {
            $form->submit($request);

            try {
                $entityManager->getConnection()->beginTransaction();

                if (!$form->isValid()) {
                    throw new \Exception('Error on form submission');
                }

                $stockItemsRepository = $entityManager->getRepository(
                    'NatueStockBundle:StockItem'
                );

                $stockItemsRepository->changeAllStockItemsForNewPosition(
                    $params
                );

                $entityManager->getConnection()->commit();

                $response = [
                    'message' => "Items were successfully moved",
                    'status'  => JsonResponse::HTTP_OK,
                ];

            } catch (\Exception $e) {
                $entityManager->getConnection()->rollback();
                $entityManager->close();

                $response = [
                    'message' => $e->getMessage(),
                    'status'  => JsonResponse::HTTP_BAD_REQUEST,
                ];
            }
        }

        $jsonResponse = new JsonResponse($response);
        $jsonResponse->setStatusCode($response['status']);

        return $jsonResponse;
    }

    /**
     * Average cost action
     *
     * @Route("/average-cost", name="stock_item_average_cost")
     * @Template()
     * @Secure(roles="ROLE_ADMIN")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Exception
     * @return array
     */
    public function averageCostAction(Request $request)
    {
        $dateTime = new \DateTime;

        if ($date = $request->query->get('date')) {
            $dateTime = new \DateTime($date);
        }

        /** @var \Natue\Bundle\StockBundle\Service\StockItemLogger $stockItemLogger */
        $stockItemLogger = $this->get('natue.stock.item.logger');

        $queryBuilder = $stockItemLogger->getQueryBuilderForAverageCostGrid($dateTime);
        $path = $this->generateUrl(
            'stock_item_average_cost',
            [
                'date' => $date,
            ]
        );

        /** @var \Natue\Bundle\StockBundle\Grid\AverageCostGrid $grid */
        $grid = $this->get('pedroteixeira.grid')->createGrid('\Natue\Bundle\StockBundle\Grid\AverageCostGrid');
        $grid->setQueryBuilder($queryBuilder);
        $grid->setUrl($path);

        if ($grid->isResponseAnswer()) {
            return $grid->render();
        }

        return [
            'grid'     => $grid->render(),
            'dateTime' => $dateTime,
        ];
    }

    /**
     * @return \Natue\Bundle\StockBundle\Grid\Datatable\ItemGrid
     */
    protected function createStockItemGrid()
    {
        /** @var ItemGridBuilder $datatableBuilder */
        $datatableBuilder = $this->get('natue.stock.item.grid.builder');

        return $datatableBuilder
            ->setAjaxSettings(
                [
                    'url'  => $this->generateUrl('stock_item_results'),
                    'type' => 'GET'
                ]
            )
            ->addSkuColumn()
            ->addQtdColumn()
            ->addNameColumn()
            ->addBarcodeColumn()
            ->addExpirationColumn()
            ->addPositionColumn()
            ->addStatusColumn()
            ->addPickableColumn()
            ->addActionsColumn()
            ->build();
    }
}
