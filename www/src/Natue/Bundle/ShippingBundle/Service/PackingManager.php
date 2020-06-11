<?php

namespace Natue\Bundle\ShippingBundle\Service;

use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;

use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\ShippingBundle\Entity\ShippingLogisticsProvider;
use Natue\Bundle\ShippingBundle\Entity\ShippingPackage;
use Natue\Bundle\ShippingBundle\Entity\ShippingVolume;
use Natue\Bundle\ShippingBundle\Repository\ShippingVolumeRepository;
use Natue\Bundle\StockBundle\Entity\StockItem;
use Natue\Bundle\StockBundle\Repository\StockItemRepository;
use Natue\Bundle\StockBundle\Service\StockItemManager;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Natue\Bundle\ZedBundle\Repository\ZedOrderRepository;
use Natue\Bundle\ZedBundle\Repository\ZedOrderItemRepository;
use Natue\Bundle\ShippingBundle\Repository\PackedOrderRepository;
use Natue\Bundle\ShippingBundle\Entity\PackedOrder;
use Natue\Bundle\ShippingBundle\Exception\ExpeditionCheckNeededException;
use Natue\Bundle\ZedBundle\Entity\EnumZedOrderItemStatusType;

class PackingManager
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
     * @var StockItemManager
     */
    protected $stockItemManager;

    /**
     * @var ObjectRepository
     */
    protected $shippingLogisticsProviderRepository;

    /**
     * @var ObjectRepository
     */
    protected $shippingPackageRepository;

    /**
     * @var ShippingVolumeRepository
     */
    protected $shippingVolumeRepository;

    /**
     * @var StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * @var PackedOrderRepository
     */
    protected $packedOrderRepository;

    /**
     * @var ZedOrderRepository
     */
    protected $zedOrderRepository;

    /**
     * @var ZedOrderItemRepository
     */
    protected $zedOrderItemRepository;

    /**
     * @param Registry         $doctrine
     * @param SecurityContext  $securityContext
     * @param StockItemManager $stockItemManager
     * @return PackingManager
     */
    public function __construct(
        Registry $doctrine,
        SecurityContext $securityContext,
        StockItemManager $stockItemManager
    ) {
        $this->entityManager = $doctrine->getManager();
        $this->user = $securityContext->getToken()->getUser();
        $this->stockItemManager = $stockItemManager;

        $this->shippingLogisticsProviderRepository = $doctrine
            ->getRepository('NatueShippingBundle:ShippingLogisticsProvider');
        $this->shippingPackageRepository           = $doctrine->getRepository('NatueShippingBundle:ShippingPackage');
        $this->shippingVolumeRepository            = $doctrine->getRepository('NatueShippingBundle:ShippingVolume');
        $this->stockItemRepository                 = $doctrine->getRepository('NatueStockBundle:StockItem');
        $this->zedOrderRepository                  = $doctrine->getRepository('NatueZedBundle:ZedOrder');
        $this->zedOrderItemRepository              = $doctrine->getRepository('NatueZedBundle:ZedOrderItem');
    }

    public function setPackedOrderRepository(PackedOrderRepository $packedOrderRepository)
    {
        $this->packedOrderRepository = $packedOrderRepository;
    }

    public function getPackedOrderRepository()
    {
        return $this->packedOrderRepository;
    }

    /**
     * @param int $logisticsProviderId
     * @throws \Exception
     * @return \Natue\Bundle\ShippingBundle\Entity\ShippingLogisticsProvider
     */
    public function findLogisticsProviderById($logisticsProviderId)
    {
        $logisticsProvider = $this->shippingLogisticsProviderRepository->findOneBy(['id' => $logisticsProviderId]);

        if (!$logisticsProvider) {
            throw new \Exception('LogisticsProvider not found.');
        }

        return $logisticsProvider;
    }

    /**
     * @param $packageId
     *
     * @return \Natue\Bundle\ShippingBundle\Entity\ShippingPackage
     * @throws \Exception
     */
    public function findPackageById($packageId)
    {
        $package = $this->shippingPackageRepository->findOneBy(['id' => $packageId]);

        if (!$package) {
            throw new \Exception('Package not found.');
        }

        return $package;
    }

    /**
     * @param ZedOrder $zedOrder
     * @throws \Exception
     * @return true
     */
    public function validateInitialSelection(ZedOrder $zedOrder)
    {
        return $this->checkOrderItemsStatus($zedOrder);
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    public function tryAssignStockItemsToNewVolume(array $data)
    {
        $zedOrder = $this->zedOrderRepository->findOneBy(['incrementId' => $data['orderIncrementId']]);

        if (
            ($this->shippingVolumeRepository->findOneByZedOrder($zedOrder) && !$data['isRecheck']) ||
            ($this->shippingVolumeRepository->findOneByZedOrder($zedOrder) && !$zedOrder->matches($data['isRecheck']))
        ) {
            throw new ExpeditionCheckNeededException("
                This order was already expedited. To resend, please submit the same order again.
            ");
        }

        if (
            (!$zedOrder->matchesShippingTariff($data['logisticsId']) && !$data['isRecheck']) ||
            (!$zedOrder->matchesShippingTariff($data['logisticsId']) && !$zedOrder->matches($data['isRecheck']))
        ) {
            throw new ExpeditionCheckNeededException("
                This order should not be shipped with this Logistic Provider.
                To send it anyway, please submit the same order again.
            ");
        }

        $shippingPackage = $this->shippingPackageRepository->find($data['packageId']);
        $trackingCode    = $data['trackingCode'];

        if ($this->hasTrackingCodeOnlinePackageTracking($trackingCode)) {
            $oldVolume = $this->shippingVolumeRepository->findOneBy(['trackingCode' => $trackingCode]);
        }

        $volume = $this->createNewVolume($zedOrder, $shippingPackage, $trackingCode);
        $this->assignStockItemsToVolume($zedOrder, $volume);
    }

    /**
     * @param string $trackingCode
     * @return boolean
     */
    private function hasTrackingCodeOnlinePackageTracking($trackingCode)
    {
        return (strcasecmp($trackingCode, 'total') != 0 &&
            strcasecmp($trackingCode, 'carrier') != 0 &&
            strcasecmp($trackingCode, 'natue') != 0);
    }

    /**
     * @param $logisticsProviderId
     * @return array
     */
    public function findOrdersForShipping($logisticsProviderId)
    {
        return $this->zedOrderRepository->findOrdersForShipping($logisticsProviderId);
    }

    public function getOrdersReadyForShipping()
    {
        $shippingLogisticsProvider = $this->shippingLogisticsProviderRepository->findAll();
        $arrayOrders = [];

        foreach ($shippingLogisticsProvider as $provider) {
            $arrayOrders[$provider->getNameInternal()] = [
                'id'       => $provider->getId(),
                'packages' => $this->shippingVolumeRepository->getPackagesInformationsByLogisticsProvider(
                    $provider->getId()
                )
            ];
        }

        return $arrayOrders;
    }

    public function getOrdersReadyForShippingByLogisticsProvider($logisticsProviderId)
    {
        return $this->zedOrderRepository->getOrdersReadyForShippingByLogisticsProvider($logisticsProviderId);
    }

    public function getCustomerName($incrementId)
    {
        return $this->zedOrderRepository->getCustomerName($incrementId);
    }

    /**
     * @param ZedOrder $zedOrder
     * @return array
     */
    public function getItemsLeftForVolume(ZedOrder $zedOrder)
    {
        return $this->zedOrderItemRepository->getItemsLeftForVolume($zedOrder);
    }

    /**
     * @param ZedOrder $zedOrder
     * @return boolean
     */
    private function checkOrderItemsStatus(ZedOrder $zedOrder)
    {
        $validStatuses = [
            EnumZedOrderItemStatusType::STATUS_INVOICE_CREATED,
            EnumZedOrderItemStatusType::STATUS_DELIVERY_FAIL,
            EnumZedOrderItemStatusType::STATUS_SHIPPED,
            EnumZedOrderItemStatusType::STATUS_WAITING_FOR_SHIPPING,
        ];

        $counts = $this->zedOrderItemRepository->countStatusesWithinGroup($zedOrder, $validStatuses);

        if ($counts['totalStockItemPacked'] == 0 || $counts['totalZedOrderItemTaken'] == 0) {
            throw new \Exception("ZedOrder was canceled or wasn't conferred.");
        } elseif ($counts['totalStockItemPacked'] != $counts['totalZedOrderItemTaken']) {
            throw new \Exception("Zed Order Items ready to ship is not equal to Stock item ready to ship");
        }

        return true;
    }

    /**
     * @param ZedOrder        $zedOrder
     * @param ShippingPackage $shippingPackage
     * @param string          $trackingCode
     * @return ShippingVolume
     */
    private function createNewVolume(ZedOrder $zedOrder, ShippingPackage $shippingPackage, $trackingCode)
    {
        $volume = new ShippingVolume();
        $volume->setZedOrder($zedOrder);
        $volume->setShippingPackage($shippingPackage);
        $volume->setTrackingCode(strtoupper($trackingCode));
        $volume->setUser($this->user);

        $this->entityManager->persist($volume);
        $this->entityManager->flush();

        return $volume;
    }

    public function registerPackingZedOrderForUser(ZedOrder $zedOrder, $user)
    {
        if (!$this->stockItemRepository->isOrderReadyToPack($zedOrder)) {
            throw new \Exception("Order is not elegible to pack");
        }

        if ($this->getPackedOrderRepository()->getByUserZedOrder($user, $zedOrder)) {
            throw new \Exception("Order already packed");
        }

        $packedOrder = new PackedOrder();
        $packedOrder->setUser($user);
        $packedOrder->setZedOrder($zedOrder);

        $this->entityManager->persist($packedOrder);
        $this->entityManager->flush();

        return $packedOrder;
    }

    /**
     * @param ZedOrder       $zedOrder
     * @param ShippingVolume $volume
     * @param array          $itemsQty
     *
     * @return array
     */
    private function assignStockItemsToVolume(ZedOrder $zedOrder, ShippingVolume $volume)
    {
        $stockItems = $this->stockItemRepository->findUnpackedItemsWithinOrder($zedOrder);

        foreach ($stockItems as $stockItem) {
            $stockItem->setShippingVolume($volume);
            $this->entityManager->persist($stockItem);
        }

        $this->entityManager->flush();
    }

    public function handlePackageCounterInSession($session, $package)
    {
        $packageCounter = $session->get('packageCounter');

        if (! is_array($packageCounter)) {
            $packageCounter = [];
        }

        if (! isset($packageCounter[$package->getName()])) {
            $packageCounter[$package->getName()] = 0;
        }

        $packageCounter[$package->getName()] += 1;

        $session->set('packageCounter', $packageCounter);

        return $packageCounter;
    }

    public function handlerExpeditionList(
        $session,
        $incrementId,
        $package,
        $customerInformation
    ) {
        $expeditionList = $session->get('expeditionList');

        if (! is_array($expeditionList)) {
            $expeditionList = [];
        }

        array_push($expeditionList, [
            'incrementId'  => $incrementId,
            'customerName' => $customerInformation['customerName'],
            'packageType'  => $package->getName(),
        ]);

        $session->set('expeditionList', $expeditionList);

        return array_reverse($expeditionList);
    }
}
