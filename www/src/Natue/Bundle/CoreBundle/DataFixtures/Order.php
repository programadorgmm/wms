<?php

namespace Natue\Bundle\CoreBundle\DataFixtures;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;

class Order
{
    /**
     * Returns order by which Fixtures are loaded
     *
     * @param OrderedFixtureInterface $object
     *
     * @return int|bool
     */
    public static function getOrder(OrderedFixtureInterface $object)
    {
        $order = [
            'Natue\Bundle\UserBundle\DataFixtures\ORM\UserData',
            'Natue\Bundle\ZedBundle\DataFixtures\ORM\ZedProductData',
            'Natue\Bundle\StockBundle\DataFixtures\ORM\PurchaseOrderData',
            'Natue\Bundle\StockBundle\DataFixtures\ORM\StockPositionData',
        ];

        return array_search(get_class($object), $order);
    }
}
