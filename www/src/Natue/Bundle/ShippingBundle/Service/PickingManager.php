<?php

namespace Natue\Bundle\ShippingBundle\Service;

use Doctrine\ORM\EntityRepository;
use Natue\Bundle\StockBundle\Entity\StockItem;
use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\PdfBundle\Service\PdfHandler;
use Natue\Bundle\PdfBundle\Service\BarcodeHandler;
use Natue\Bundle\ShippingBundle\Entity\ShippingPickingList;
use Natue\Bundle\ShippingBundle\Repository\ShippingPickingListRepository;
use Natue\Bundle\ShippingBundle\Entity\ShippingLogisticsProvider;
use Natue\Bundle\StockBundle\Repository\StockItemRepository;
use Natue\Bundle\StockBundle\Service\StockItemManager;
use Natue\Bundle\ZedBundle\Entity\OrderExtended;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Natue\Bundle\ZedBundle\Entity\ZedOrderItemStatus;
use Natue\Bundle\ZedBundle\Repository\ZedOrderRepository;
use Natue\Bundle\ZedBundle\Repository\ZedOrderItemRepository;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;
use Natue\Bundle\ZedBundle\Entity\EnumZedOrderItemStatusType;

/**
 * @package Natue\Bundle\ShippingBundle\Service
 */
class PickingManager
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var User
     */
    private $user;

    /**
     * @var StockItemManager
     */
    private $stockItemManager;

    /**
     * @var PdfHandler
     */
    private $pdfHandlerService;

    /**
     * @var BarcodeHandler
     */
    private $barcodeHandler;

    /**
     * @var ZedOrderRepository
     */
    private $zedOrderRepository;

    /**
     * @var StockItemRepository
     */
    private $stockItemRepository;

    /**
     * @var ShippingPickingListRepository
     */
    private $shippingPickingListRepository;

    /**
     * @var ZedOrderItemRepository
     */
    private $zedOrderItemRepository;

    /**
     * @var ZedOrderItemStatusRepository
     */
    private $zedOrderItemStatusRepository;
    /**
     * @var EntityRepository
     */
    private $orderExtendedRepository;

    /**
     * @param Registry         $doctrine
     * @param SecurityContext  $securityContext
     * @param StockItemManager $stockItemManager
     * @param PdfHandler       $pdfHandlerService
     * @param BarcodeHandler   $barcodeHandler
     *
     * @return PickingManager
     */
    public function __construct(
        Registry $doctrine,
        SecurityContext $securityContext,
        StockItemManager $stockItemManager,
        PdfHandler $pdfHandlerService,
        BarcodeHandler $barcodeHandler
    ) {
        $this->doctrine      = $doctrine;
        $this->entityManager = $doctrine->getManager();

        $this->user = $securityContext->getToken()->getUser();

        $this->stockItemManager  = $stockItemManager;
        $this->pdfHandlerService = $pdfHandlerService;
        $this->barcodeHandler    = $barcodeHandler;

        $this->zedOrderRepository            = $doctrine->getRepository('NatueZedBundle:ZedOrder');
        $this->zedOrderItemRepository        = $doctrine->getRepository('NatueZedBundle:ZedOrderItem');
        $this->zedOrderItemStatusRepository  = $doctrine->getRepository('NatueZedBundle:ZedOrderItemStatus');
        $this->orderExtendedRepository       = $doctrine->getRepository('NatueZedBundle:OrderExtended');
        $this->stockItemRepository           = $doctrine->getRepository('NatueStockBundle:StockItem');
        $this->shippingPickingListRepository = $doctrine->getRepository('NatueShippingBundle:ShippingPickingList');
    }

    /**
     * @param $ordersList
     *
     * @throws \Exception
     * @return ShippingPickingList
     */
    public function tryBuildShippingPickingListForOrderIdsList($ordersList)
    {
        $orders = new ArrayCollection(
            $this->zedOrderRepository->getNotExtendedOrdersWithinIdsList(
                explode(',', $ordersList)
            )
        );

        if (count($orders) == 0) {
            throw new \Exception('There is no Orders to pick');
        }

        return $this->buildPickingListForOrders($orders);
    }

    /**
     * @param ShippingLogisticsProvider $provider
     * @param int $requestedOrdersAmount
     * @param boolean $requestedMonoSku
     * @throws \Exception
     *
     * @return ShippingPickingList
     */
    public function tryToBuildShippingPickingListForProvider(
        ShippingLogisticsProvider $provider,
        $requestedOrdersAmount,
        $requestedMonoSku = false
    ) {
        $validStatuses = new ArrayCollection(
            $this->zedOrderItemStatusRepository->findBy([
                'name' => [
                    EnumZedOrderItemStatusType::STATUS_READY_FOR_PICKING,
                    EnumZedOrderItemStatusType::STATUS_CLARIFY_PICKING_FAILED,
                    EnumZedOrderItemStatusType::STATUS_INVOICE_CREATED,
                ]
            ])
        );

        /**
         * @var ArrayCollection $caughtOrders
         */
        $caughtOrders = $this->getOldestAssignedForPickingOrdersByProviderWithLimit(
            $provider,
            $requestedOrdersAmount,
            $validStatuses->map(function ($s) {
                return $s->getId();
            })->toArray(),
            $requestedMonoSku
        );

        if ($caughtOrders->isEmpty()) {
            return null;
        }

        return $this->buildPickingListForOrders($caughtOrders);
    }

    /**
     * @param array $failures
     */
    public function removeOrdersOfPickingList(array $failures)
    {
        $this->removeOrdersExtended($failures);
        $this->moveItemsToAssigned($failures);

        $this->entityManager->flush();
    }

    /**
     * @param ShippingLogisticsProvider $provider
     * @param int $limit
     * @param array $validStatuses
     * @param boolean $requestedMonoSku
     * @return array
     */
    private function getOldestAssignedForPickingOrdersByProviderWithLimit(
        $provider,
        $limit,
        $validStatuses,
        $requestedMonoSku
    ) {
        return new ArrayCollection($this->zedOrderRepository->getOldestAssignedForPickingOrdersByProviderWithLimit(
            $provider,
            $limit,
            $validStatuses,
            ['isMonoSku' => $requestedMonoSku] // options, pass by array!
        ));
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getShippingPickingListQueryBuilder()
    {
        return $this->shippingPickingListRepository->getDataArrayForListPickingLists();
    }

    /**
     * @param ShippingPickingList $shippingPickingList
     * @param boolean $isMonoSku
     *
     * @return array
     */
    public function generatePdfFilesForPickingList(
        ShippingPickingList $shippingPickingList,
        $isMonoSku = false
    ) {
        $pickingItems = $this->buildPickingList($shippingPickingList, $isMonoSku);

        $failures = $this->generateInvoicesPdf($pickingItems);
        $pickingItems = $this->rebuildPickingList($pickingItems, $failures);

        $this->generatePickingListPdf($pickingItems);
        $this->generateExpeditionLabelsPdf($pickingItems);

        return $failures;
    }

    /**
     * @param array $pickingList
     * @param array $failures
     *
     * @return array
     */
    private function rebuildPickingList(array $pickingList, array $failures)
    {
        $lastBoxNumber = 0;
        foreach ($pickingList['customersMap'] as $incrementID => $order) {
            if (in_array($incrementID, $failures)) {
                unset($pickingList['customersMap'][$incrementID]);
                continue;
            }

            if (($order['tempBoxNumber'] - $lastBoxNumber) > 1) {
                $pickingList['customersMap'][$incrementID]['tempBoxNumber'] = $lastBoxNumber + 1;
            }

            $lastBoxNumber = $order['tempBoxNumber'];
        }

        foreach ($pickingList['items'] as $key => $item) {
            if (in_array($item['incrementId'], $failures)) {
                unset($pickingList['items'][$key]);
            }
        }

        return $pickingList;
    }

    /**
     * @param ShippingPickingList $shippingPickingList
     *
     * @return array
     */
    private function buildPickingList(
        ShippingPickingList $shippingPickingList,
        $isMonoSku
    ) {
        if ($isMonoSku) {
            $pickingList = $this->makePickListForMonoSku($shippingPickingList);
        } else {
            $pickingList = $this->makePickList($shippingPickingList);
        }

        return $pickingList;
    }

    /**
     * @param ShippingPickingList $shippingPickingList
     * @return array
     */
    private function makePickListForMonoSku(ShippingPickingList $shippingPickingList)
    {
        $items             = $this->shippingPickingListRepository->getArrayDataForMonoSkuPickingList($shippingPickingList);
        $consolidatedItems = $this->sumZedOrderItemQuantity($items);
        $waitingForPickingItems = array_reduce($consolidatedItems, function ($currentAmount, array $item) {
            return $currentAmount + $item['quantity'];
        }, 0);
        $monoSku           = true;

        return [
            'pickingListId' => $shippingPickingList->getId(),
            'created_at'    => $shippingPickingList->getCreatedAt(),
            'user'          => $shippingPickingList->getUser()->getName(),
            'items'         => $consolidatedItems,
            'quantity'      => $waitingForPickingItems,
            'customersMap'  => $this->buildCustomerOrdersMapForMonuSku($items),
            'isMonoSku'     => $monoSku,
        ];
    }

    /**
     * @param ShippingPickingList $shippingPickingList
     * @return array
     */
    private function makePickList(ShippingPickingList $shippingPickingList)
    {
        $items         = $this->shippingPickingListRepository->getDataArrayForPickingList($shippingPickingList);
        $sortedItems   = $this->sortItemsByQuantity($items);
        $waitingForPickingItems = array_reduce($sortedItems, function ($currentAmount, array $item) {
            return $currentAmount + $item['quantity'];
        }, 0);
        $monoSku       = false;

        return [
            'pickingListId' => $shippingPickingList->getId(),
            'created_at'    => $shippingPickingList->getCreatedAt(),
            'user'          => $shippingPickingList->getUser()->getName(),
            'items'         => $sortedItems,
            'quantity'      => $waitingForPickingItems,
            'customersMap'  => $this->buildCustomerOrdersMap($items),
            'isMonoSku'     => $monoSku,
        ];
    }

    /**
     * @param array $pickingListData
     */
    private function generatePickingListPdf(array $pickingListData)
    {
        if ($pickingListData['isMonoSku']) {
            $this->pdfHandlerService->createShippingPickingListPdfForMonoSku($pickingListData);
        } else {
            $this->pdfHandlerService->createShippingPickingListPdf($pickingListData);
        }
    }

    /**
     * @param array $pickingListData
     */
    private function generateExpeditionLabelsPdf(array $pickingListData)
    {
        foreach ($pickingListData['customersMap'] as $key => $order) {
            $pickingListData['customersMap'][$key]['barcodePath'] = $this->barcodeHandler->generateBarcode($order['incrementId']);
        }

        $this->pdfHandlerService->createExpeditionLabelsPdf($pickingListData['pickingListId'], ['orders' => $pickingListData['customersMap']]);
    }

    /**
     * @param array $pickingListData
     *
     * @return array
     */
    private function generateInvoicesPdf(array $pickingListData)
    {
        foreach ($pickingListData['customersMap'] as $key => $order) {
            $pickingListData['customersMap'][$key]['invoiceKey'] = $order['invoiceKey'];
        }

        return
            $this
                ->pdfHandlerService
                ->createInvoicesPdf($pickingListData['pickingListId'], ['orders' => $pickingListData['customersMap']]);
    }

    /**
     * @param int $shippingPickingListId
     * @return null|object
     */
    public function getShippingPickingListById($shippingPickingListId)
    {
        return $this->shippingPickingListRepository->find($shippingPickingListId);
    }

    /**
     * @param int $orderId
     * @return ZedOrder
     */
    public function findOrderById($orderId)
    {
        return $this->zedOrderRepository->find($orderId);
    }

    /**
     * @param $incrementId
     * @return array
     */
    public function findOrderByIncrementId($incrementId)
    {
        return $this->zedOrderRepository->findOneBy(compact('incrementId'));
    }

    /**
     * @param ZedOrder $order
     * @return int
     */
    public function getTotalItemsForZedOrder(ZedOrder $order)
    {
        return $this->stockItemRepository->getCountTotalItemsForZedOrder($order);
    }

    /**
     * @param ZedOrder $zedOrder
     * @param array    $userBarcodesCount
     *
     * @return array
     */
    public function validateOrderBarcodesCount(ZedOrder $zedOrder, array $userBarcodesCount)
    {
        $itemsId = $zedOrder->getZedOrderItems()->map(function ($i) {
            return $i->getId();
        })->toArray();

        foreach ($this->stockItemRepository->findByZedOrderItem($itemsId) as $stockItem) {
            $stockItems[$stockItem->getId()] = $stockItem->getBarcode();
        }

        foreach ($userBarcodesCount as $barcode => $qty) {
            for ($i=1; $i<=$qty; $i++) {
                $index = array_search($barcode, $stockItems);
                unset($stockItems[$index]);
            }
        }

        $errors = [];

        if (!empty($stockItems)) {
            $errors['message'] = 'Some items were not scanned';
            $errors['missingItems'] = $this->stockItemRepository
                ->getByBarcodeAndZedOrder(array_keys($stockItems), $zedOrder);
        }

        return $errors;
    }

    /**
     * @param string   $barcode
     * @param ZedOrder $zedOrder
     *
     * @return bool
     */
    public function isBarcodeWithinOrder($barcode, ZedOrder $zedOrder)
    {
        return (bool)$this->zedOrderRepository->getOrderByItemBarcodeAndPk($barcode, $zedOrder);
    }

    /**
     * @param          $barcode
     * @param ZedOrder $zedOrder
     *
     * @return bool
     */
    public function isBarcodeHasStockStatus($barcode, ZedOrder $zedOrder)
    {
        return (bool)$this->zedOrderRepository->isBarcodeHasStockStatus(
            $zedOrder,
            $barcode,
            EnumStockItemStatusType::STATUS_WAITING_FOR_PICKING
        );
    }

    /**
     * @param string         $barcode
     * @param ZedOrder       $zedOrder
     * @param ScannerStorage $scannerStorage
     *
     * @return mixed
     */
    public function isElegibleToValidate($barcode, ZedOrder $zedOrder, ScannerStorage $scannerStorage)
    {
        $barcodeTotal = $this->zedOrderRepository->getTotalProductsForZedOrderAndBarcode($zedOrder, $barcode);

        return $scannerStorage->canReceiveBarcode($zedOrder->getId(), $barcode, $barcodeTotal);
    }

    /**
     * @param ZedOrder $zedOrder
     * @return boolean
     */
    public function isReadyToPick(ZedOrder $zedOrder)
    {
        return $this->stockItemRepository->isReadyToPick($zedOrder);
    }

    /**
     * @return ShippingPickingList
     */
    private function createShippingPickingList()
    {
        $shippingPickingList = new ShippingPickingList();
        $shippingPickingList->setUser($this->user);

        $this->entityManager->persist($shippingPickingList);
        $this->entityManager->flush();

        return $shippingPickingList;
    }

    /**
     * @param array               $zedOrders
     * @param ShippingPickingList $shippingPickingList
     * @return void
     */
    private function createOrdersExtended(array $zedOrders, ShippingPickingList $shippingPickingList)
    {
        /** @var ZedOrder $zedOrder */
        foreach ($zedOrders as $zedOrder) {
            $orderExtended = new OrderExtended();
            $orderExtended->setZedOrder($zedOrder);
            $orderExtended->setShippingPickingList($shippingPickingList);

            $this->entityManager->persist($orderExtended);
            $this->entityManager->flush();
        }
    }

    /**
     * @param array $failures
     */
    private function removeOrdersExtended(array $failures)
    {
        /**
         * @var ZedOrder $order
         */
        foreach ($failures as $incrementId) {
            $order = $this->zedOrderRepository->findOneByIncrementId($incrementId);

            $order->removeOrderExtended();
        }
    }

    /**
     * @param array $failures
     */
    private function moveItemsToAssigned(array $failures)
    {
        foreach ($failures as $incrementId) {
            /**
             * @var ZedOrder $order
             */
            $order = $this->zedOrderRepository->findOneByIncrementId($incrementId);
            $items = $this->stockItemRepository->getItemsByZedOrder($order);

            /**
             * @var StockItem $item
             */
            foreach ($items as $item) {
                $item->setStatus(EnumStockItemStatusType::STATUS_ASSIGNED);

                $this->entityManager->persist($item);
            }
        }
    }

    /**
     * @param ZedOrder $zedOrder
     * @return array
     */
    private function groupAndCountBarcodesFromOrder(ZedOrder $zedOrder)
    {
        $orderBarcodesCount = $this->stockItemRepository->barcodesGroupAndCountWithinOrder($zedOrder);
        $result = [];

        foreach ($orderBarcodesCount as $row) {
            $result[$row['barcode']] = (int)$row['amount'];
        }

        return $result;
    }

    /**
     * @param array $items
     * @return array
     */
    private function buildCustomerOrdersMap(array $items)
    {
        $customerMap   = [];
        $tempNumbering = 0;

        foreach ($items as $row) {
            if (array_key_exists($row['incrementId'], $customerMap)) {
                continue;
            }

            $tempNumbering += 1;

            $customerMap[$row['incrementId']] = [
                'tempBoxNumber' => $tempNumbering,
                'customerName'  => $row['customerName'],
                'incrementId'   => $row['incrementId'],
                'addressLine1' => $row['addressLine1'],
                'customerAdditional' => $row['customerAdditional'],
                'quarter' => $row['quarter'],
                'city' => $row['city'],
                'state' => $row['state'],
                'zipcode' => $row['zipcode'],
                'addressReference' => $row['addressReference'],
                'tariffName' => $row['tariffName'],
                'invoiceKey' => $row['invoiceKey'],
            ];
        }

        return $customerMap;
    }

    /**
     * @param array $items
     * @return array
     */
    private function buildCustomerOrdersMapForMonuSku(array $items)
    {
        $preMap   = [];

        foreach ($items as $row) {

            $preMap[$row['productId']][] = [
                'customerName'  => $row['customerName'],
                'totalItems'    => $row['total'],
                'productName'   => $row['productName'],
                'incrementId'   => $row['incrementId'],
                'addressLine1' => $row['addressLine1'],
                'customerAdditional' => $row['customerAdditional'],
                'quarter' => $row['quarter'],
                'city' => $row['city'],
                'state' => $row['state'],
                'zipcode' => $row['zipcode'],
                'addressReference' => $row['addressReference'],
                'tariffName' => $row['tariffName'],
                'invoiceKey' => $row['invoiceKey'],
            ];
        }

        return $this->reorganizeCustomerMap($preMap);
    }

    /**
     * @param array $preMap
     * @return array
     */
    private function reorganizeCustomerMap(array $preMap)
    {
        return array_reduce($preMap, function (array $customMap, array $arrayZedProduct) {
            return array_merge($customMap, array_values($arrayZedProduct));
        }, []);
    }

    /**
     * @return ShippingPickingList
     */
    private function buildPickingListForOrders(ArrayCollection $orders)
    {
        foreach ($orders as $zedOrder) {
            $this->stockItemManager->markZedOrderAsWaitingForPicking($zedOrder);
        }

        $shippingPickingList = $this->createShippingPickingList();
        $this->createOrdersExtended($orders->toArray(), $shippingPickingList);

        return $shippingPickingList;
    }

    /**
     * @param array $items
     * @return array
     */
    private function sumZedOrderItemQuantity(array $items)
    {
        $itemsFiltered = [];

        foreach ($items as $item) {
            $index = $item['productId'] . '_' . $item['positionName'];
            if (array_key_exists($index, $itemsFiltered)) {
                $itemsFiltered[$index]['quantity'] += $item['quantity'];
                continue;
            }

            $itemsFiltered[$index] = $item;
        }

        return $itemsFiltered;
    }

    /**
     * @param array $items
     * @return array
     */
    private function sortItemsByQuantity(array $items)
    {
        $sortedItems = [];

        foreach ($items as $item) {
            if (
                array_key_exists($item['idZedOrderItem'], $sortedItems)
                &&
                !$this->hasMinorStockQuantityThanCurrentLowest(
                    $sortedItems[$item['idZedOrderItem']]['quantity'],
                    $item['quantity']
                )
            ) {
                continue;
            }
            $sortedItems[$item['idZedOrderItem']] = $item;
        }

        return $sortedItems;
    }

    /**
     * @param int $lowestQuantity
     * @param int $itemQuantity
     * @return bool
     */
    private function hasMinorStockQuantityThanCurrentLowest($lowestQuantity, $itemQuantity)
    {
        return $itemQuantity < $lowestQuantity;
    }
}
