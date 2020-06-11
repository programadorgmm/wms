<?php

namespace Natue\Bundle\ShippingBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Natue\Bundle\StockBundle\Entity\ColumnType\EnumStockItemStatusType;

/**
 * Class ShippingVolumeRepository
 * @package Natue\Bundle\ShippingBundle\Repository
 */
class ShippingVolumeRepository extends EntityRepository
{
    /**
     * @param ZedOrder $zedOrder
     *
     * @return array
     */
    public function getOrderVolumesContent(ZedOrder $zedOrder)
    {
        $query = $this->createQueryBuilder('shippingVolume')
            ->select('shippingVolume.trackingCode AS trackingCode')
            ->addSelect('zedProduct.sku AS sku')
            ->addSelect('COUNT(stockItem.id) AS qty')
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.shippingVolume = shippingVolume.id'
            )
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'stockItem.zedProduct = zedProduct.id'
            )
            ->where('shippingVolume.zedOrder = :zedOrder')
            ->groupBy('shippingVolume.id')
            ->addGroupBy('zedProduct.id')
            ->setParameters(
                [
                    'zedOrder' => $zedOrder,
                ]
            )
            ->getQuery();

        return $query->getResult();
    }


    /**
     * @param int $logisticsProviderId
     *
     * @return array
     */
    public function getPackagesInformationsByLogisticsProvider($logisticsProviderId)
    {
        $query = $this->createQueryBuilder('shippingVolume')
            ->select('shippingPackage.id')
            ->addSelect('shippingPackage.name')
            ->addSelect('count( distinct shippingVolume.id) AS total')
            ->innerJoin(
                'NatueShippingBundle:ShippingPackage',
                'shippingPackage',
                'WITH',
                'shippingPackage.id = shippingVolume.shippingPackage'
            )
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.shippingVolume = shippingVolume.id'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrder',
                'zedOrder',
                'WITH',
                'zedOrder.id = shippingVolume.zedOrder'
            )
            ->innerJoin(
                'NatueShippingBundle:ShippingTariff',
                'shippingTariff',
                'WITH',
                'shippingTariff.id = zedOrder.shippingTariffCode'
            )
            ->innerJoin(
                'NatueShippingBundle:ShippingLogisticsProvider',
                'shippingLogisticsProvider',
                'WITH',
                'shippingTariff.logisticsProvider = shippingLogisticsProvider.id AND
                 shippingLogisticsProvider.id = :logisticsProvider'
            )
            ->where('stockItem.status = :status')
            ->groupBy('shippingPackage.name')
            ->setParameters(
                [
                    'status'            => EnumStockItemStatusType::STATUS_READY_FOR_SHIPPING,
                    'logisticsProvider' => $logisticsProviderId
                ]
            )
            ->getQuery();

        return $query->getResult();
    }

    /**
    * @param int $logisticsProviderId
    * @param int $packageId
    *
    * @return array
    */
    public function checkDuplicityPackage($logisticsProviderId)
    {
        $query = $this->createQueryBuilder('shippingVolume')
        ->select('CASE WHEN count(shippingVolume.id) > count(distinct zedOrder.id)
                       THEN true ELSE false END AS checkDuplicity')
        ->leftJoin(
            'NatueZedBundle:ZedOrder',
            'zedOrder',
            'WITH',
            'shippingVolume.zedOrder = zedOrder.id'
        )
        ->leftJoin(
            'NatueShippingBundle:ShippingTariff',
            'shippingTariff',
            'WITH',
            'zedOrder.shippingTariffCode = shippingTariff.id'
        )
        ->innerJoin(
            'NatueZedBundle:ZedOrderItemReadyForShipping',
            'zedOrderItemReadyForShipping',
            'WITH',
            'shippingVolume.zedOrder = zedOrderItemReadyForShipping.zedOrder'
        )
        ->where('shippingTariff.logisticsProvider = :logisticsProvider')
        ->andWhere("DATE(shippingVolume.createdAt) between
                            :firstDate
                            and DATE(CURRENT_DATE())")
        ->setParameters(
            [
                'logisticsProvider' => $logisticsProviderId,
                'firstDate'         => new \DateTime('-6 days')
            ]
        )
        ->getQuery();

        return $query->getSingleResult();
    }
}
