<?php

namespace Natue\Bundle\ShippingBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Natue\Bundle\ShippingBundle\Entity\ShippingPickingList;

/**
 * Class ShippingPickingListRepository
 * @package Natue\Bundle\ShippingBundle\Repository
 */
class ShippingPickingListRepository extends EntityRepository
{
    /**
     * string (php textual datetime description)
     */
    const EXPIRATION_WARNING_PERIOD = "+20 days";

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDataArrayForListPickingLists()
    {
        return $this->createQueryBuilder('shippingPickingList')
            ->addSelect('shippingPickingList.id')
            ->addSelect('shippingPickingList.createdAt')
            ->addSelect('user.name AS userName')
            ->innerJoin(
                'NatueUserBundle:User',
                'user',
                'WITH',
                'shippingPickingList.user = user.id'
            );
    }

    /**
    * @param ShippingPickingList $shippingPickingList
    * @return array
    */
    public function getDataArrayForPickingList(ShippingPickingList $shippingPickingList)
    {
        $query = $this->getShippingPickingListQuery($shippingPickingList);
        $query = $query
            ->addGroupBy('stockPosition.id')
            ->addGroupBy('zedOrderItem.zedOrder');

        return $query->getQuery()->getArrayResult();
    }

    /**
     * @param ShippingPickingList $shippingPickingList
     * @return array
     */
    public function getArrayDataForMonoSkuPickingList(ShippingPickingList $shippingPickingList)
    {
        return $this->getShippingPickingListQuery($shippingPickingList)->getQuery()->getArrayResult();
    }

    /**
     * @param ShippingPickingList $shippingPickingList
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getShippingPickingListQuery(ShippingPickingList $shippingPickingList)
    {
        return $this->createQueryBuilder('shippingPickingList')
            ->select('stockPosition.name AS positionName')
            ->addSelect('COUNT(distinct stockItem.zedOrderItem) AS quantity')
            ->addSelect('stockItem.barcode')
            ->addSelect('COUNT(zedProduct.sku) AS total')
            ->addSelect('zedOrderItem.id as idZedOrderItem')
            ->addSelect('zedProduct.name AS productName')
            ->addSelect('zedProduct.brand')
            ->addSelect('zedProduct.id AS productId')
            ->addSelect('zedOrder.incrementId')
            ->addSelect('zedOrder.fkSubscription')
            ->addSelect('(CASE WHEN stockItem.dateExpiration <= :warningDate THEN 1 ELSE 0 END) AS checkExpirationFlag')
            ->addSelect("CONCAT_WS(' ', zedOrder.customerFirstname, zedOrder.customerLastname) AS customerName")
            ->addSelect("CONCAT_WS(', ', zedOrder.customerAddress1, zedOrder.customerAddress2) AS addressLine1")
            ->addSelect('zedOrder.customerAdditional AS customerAdditional')
            ->addSelect('zedOrder.customerQuarter AS quarter')
            ->addSelect('zedOrder.customerCity AS city')
            ->addSelect('zedOrder.customerState AS state')
            ->addSelect('zedOrder.customerZipcode AS zipcode')
            ->addSelect('zedOrder.customerAddressReference AS addressReference')
            ->addSelect('shippingTariff.name AS tariffName')
            ->addSelect('zedOrder.invoiceKey AS invoiceKey')
            ->innerJoin(
                'NatueZedBundle:OrderExtended',
                'orderExtended',
                'WITH',
                'orderExtended.shippingPickingList = shippingPickingList.id'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrder',
                'zedOrder',
                'WITH',
                'zedOrder.id = orderExtended.zedOrder'
            )
            ->innerJoin(
                'NatueZedBundle:ZedOrderItem',
                'zedOrderItem',
                'WITH',
                'zedOrderItem.zedOrder = zedOrder.id'
            )
            ->innerJoin(
                'NatueStockBundle:StockItem',
                'stockItem',
                'WITH',
                'stockItem.zedOrderItem = zedOrderItem.id'
            )
            ->innerJoin(
                'NatueZedBundle:ZedProduct',
                'zedProduct',
                'WITH',
                'zedProduct.id = stockItem.zedProduct'
            )
            ->innerJoin(
                'NatueStockBundle:StockPosition',
                'stockPosition',
                'WITH',
                'stockPosition.id = stockItem.stockPosition'
            )
            ->innerJoin(
                'NatueShippingBundle:ShippingTariff',
                'shippingTariff',
                'WITH',
                'shippingTariff.id = zedOrder.shippingTariffCode'
            )
            ->where('shippingPickingList.id = :shippingPickingListId')
            ->groupBy('zedProduct.sku')
            ->addGroupBy('zedOrder.incrementId')
            ->orderBy('stockPosition.sort')
            ->addOrderBy('stockItem.id')
            ->setParameters(
                [
                    'shippingPickingListId' => $shippingPickingList->getId(),
                    'warningDate'           => Date('Y-m-d', strtotime(self::EXPIRATION_WARNING_PERIOD)),
                ]
            );
    }
}
