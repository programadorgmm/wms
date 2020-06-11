<?php

namespace Natue\Bundle\CoreBundle\Tests;

use Natue\Bundle\StockBundle\Entity\PurchaseOrder;
use Natue\Bundle\StockBundle\Entity\PurchaseOrderItem;
use Natue\Bundle\StockBundle\Entity\StockItem;
use Natue\Bundle\StockBundle\Entity\StockPosition;
use Natue\Bundle\UserBundle\Entity\Group;
use Natue\Bundle\UserBundle\Entity\User;
use Natue\Bundle\ZedBundle\Entity\ZedOrder;
use Natue\Bundle\ZedBundle\Entity\ZedOrderItem;
use Natue\Bundle\ZedBundle\Entity\ZedOrderItemStatus;
use Natue\Bundle\ZedBundle\Entity\ZedProduct;
use Natue\Bundle\ZedBundle\Entity\ZedSupplier;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as WTC;

/**
 * Extending Sf's WebTestCase
 */
class WebTestCase extends WTC
{
    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    public static $client = null;

    /** @var \Symfony\Component\Security\Core\SecurityContext */
    public static $user = null;

    /** @var \Doctrine\Bundle\DoctrineBundle\Registry */
    public static $doctrine = null;

    /* @var \Doctrine\ORM\EntityManager $entityManager */
    public static $entityManager = null;

    /* @var \Symfony\Bundle\FrameworkBundle\Routing\Router */
    public static $router = null;

    /* @var \Symfony\Bundle\FrameworkBundle\Translation\Translator */
    public static $translator = null;

    /* @var \Symfony\Component\DependencyInjection\Container */
    public static $container = null;

    /**
     * Setup
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        self::$client        = self::createAuthenticatedClient();
        self::$user          = self::$kernel->getContainer()->get('security.context')->getToken()->getUser();
        self::$doctrine      = self::$kernel->getContainer()->get('doctrine');
        self::$entityManager = self::$kernel->getContainer()->get('doctrine')->getManager();
        self::$router        = self::$kernel->getContainer()->get('router');
        self::$translator    = self::$kernel->getContainer()->get('translator');
        self::$container     = self::$kernel->getContainer();
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method     = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Set protected/private variable
     *
     * @return void
     */
    protected function setVariable($object, $propertyName, $propertyValue)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property   = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $propertyValue);
    }

    /**
     * Create an Authenticated Client
     *
     * @param string $username username
     * @param string $password password
     *
     * @return \Symfony\Bundle\FrameworkBundle\Client Client
     */
    protected static function createAuthenticatedClient($username = 'admin', $password = 'admin')
    {
        $options = [];

        // used by phpunit_circleci.xml
        if (isset($_ENV['APPLICATION_ENV'])) {
            $options['environment'] = $_ENV['APPLICATION_ENV'];
        }

        $client = static::createClient($options);
        $client->followRedirects(true);

        $form              = $client->request('GET', '/login')->filterXpath('//button[@type="submit"]')->form();
        $form['_username'] = $username;
        $form['_password'] = $password;
        $client->submit($form);

        return $client;
    }

    /**
     * @param array $values
     *
     * @return Group
     */
    protected function groupFactory($values = [])
    {
        $defaultValues = array_merge(
            [
                'Name' => 'Group #' . uniqid(),
                'Role' => ['ROLE_SUPER_ADMIN']
            ],
            $values
        );

        $group = new Group($defaultValues['Name'], $defaultValues['Role']);

        self::$entityManager->persist($group);
        self::$entityManager->flush();

        return $group;
    }

    /**
     * @param array $values
     *
     * @return User
     */
    protected function userFactory($values = [])
    {
        $defaultValues = array_merge(
            [
                'Username' => 'user#' . uniqid(),
                'Name'     => 'Name #' . uniqid(),
                'Email'    => uniqid() . '@email.com',
                'Enabled'  => 'true',
                'Groups'   => [$this->groupFactory()],
                'Password' => 'test123'
            ],
            $values
        );

        $user = new User();
        foreach ($defaultValues as $method => $param) {
            if ($method == 'Groups') {
                foreach ($param as $group) {
                    $user->addGroup($group);
                }
            } else {
                $setMethod = "set$method";

                if (method_exists($user, $setMethod)) {
                    $user->$setMethod($param);
                }
            }
        }

        self::$entityManager->persist($user);
        self::$entityManager->flush();

        return $user;
    }

    /**
     * @param array $values
     *
     * @return StockPosition
     */
    protected function stockPositionFactory($values = [])
    {
        $defaultValues = array_merge(
            [
                'Name'      => 'Name #' . uniqid(),
                'Sort'     => rand(1, 1000),
                'Pickable'  => true,
                'Inventory' => false,
                'Enabled'   => true,
                'User'      => self::$user
            ],
            $values
        );

        $position = new StockPosition();

        foreach ($defaultValues as $method => $param) {
            $setMethod = "set$method";

            if (method_exists($position, $setMethod)) {
                $position->$setMethod($param);
            }
        }
        self::$entityManager->persist($position);
        self::$entityManager->flush();

        return $position;
    }

    /**
     * @param array $values
     *
     * @return StockItem
     */
    protected function stockItemFactory($values = [])
    {
        $defaultValues = [
            'ZedProduct'        => $this->zedProductFactory(),
            'PurchaseOrderItem' => $this->purchaseOrderItemFactory(),
            'DateExpiration'    => new \DateTime("+10 days"),
            'Barcode'           => uniqid()
        ];

        $values = array_merge($defaultValues, $values);

        $stockItem = new StockItem();

        foreach ($values as $method => $param) {
            $setMethod = "set$method";

            if (method_exists($stockItem, $setMethod)) {
                $stockItem->$setMethod($param);
            }
        }
        self::$entityManager->persist($stockItem);
        self::$entityManager->flush();

        return $stockItem;
    }

    /**
     * @param array $values
     *
     * @return PurchaseOrder
     */
    protected function purchaseOrderFactory($values = [])
    {
        $defaultValues = array_merge(
            [
                'VolumesTotal' => 2,
                'CostTotal'    => 1010,
                'User'         => self::$user
            ],
            $values
        );

        $purchaseOrder = new PurchaseOrder();

        foreach ($defaultValues as $method => $param) {
            $setMethod = "set$method";

            if (method_exists($purchaseOrder, $setMethod)) {
                $purchaseOrder->$setMethod($param);
            }
        }
        self::$entityManager->persist($purchaseOrder);
        self::$entityManager->flush();

        return $purchaseOrder;
    }

    /**
     * @param array $values
     *
     * @return PurchaseOrderItem
     */
    protected function purchaseOrderItemFactory($values = [])
    {
        $defaultValues = array_merge(
            [
                'Cost'          => 1,
                'PurchaseOrder' => $this->purchaseOrderFactory(),
                'ZedProduct'    => $this->zedProductFactory()
            ],
            $values
        );

        $purchaseOrderItem = new PurchaseOrderItem();

        foreach ($defaultValues as $method => $param) {
            $setMethod = "set$method";

            if (method_exists($purchaseOrderItem, $setMethod)) {
                $purchaseOrderItem->$setMethod($param);
            }
        }
        self::$entityManager->persist($purchaseOrderItem);
        self::$entityManager->flush();

        return $purchaseOrderItem;
    }

    /**
     * @param array $values
     *
     * @return ZedProduct
     */
    protected function zedProductFactory($values = array())
    {
        $date = new \DateTime();
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository          = self::$entityManager->getRepository('NatueZedBundle:ZedProduct');
        $queryBuilder        = $repository->createQueryBuilder('zedProduct')
            ->select('MAX(zedProduct.id)');
        $zedProductHighestId = $queryBuilder->getQuery()->getSingleScalarResult();

        $defaultValues = array_merge(
            [
                'Id'           => $zedProductHighestId + 1,
                'CreatedAt'    => $date,
                'Sku'          => 'SKU #' . uniqid(),
                'Name'         => 'Name #' . uniqid(),
                'Brand'        => 'Brand #' . uniqid(),
                'Status'       => 'Status #' . uniqid(),
                'AttributeSet' => '1',
                'GrossWeight'  => 1,
                'IsBook'       => false,
                'IsSt'         => false,
                'ZedSupplier'  => $this->zedSupplierFactory()
            ],
            $values
        );

        $zedProduct = new ZedProduct();

        foreach ($defaultValues as $method => $param) {
            $setMethod = "set$method";

            if (method_exists($zedProduct, $setMethod)) {
                $zedProduct->$setMethod($param);
            }
        }

        self::$entityManager->persist($zedProduct);
        self::$entityManager->flush();

        return $zedProduct;
    }

    /**
     * @param array $values
     *
     * @return ZedSupplier
     */
    protected function zedSupplierFactory($values = array())
    {
        $date = new \DateTime();
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository           = self::$entityManager->getRepository('NatueZedBundle:ZedSupplier');
        $queryBuilder         = $repository->createQueryBuilder('zedSupplier')
            ->select('MAX(zedSupplier.id)');
        $zedSupplierHighestId = $queryBuilder->getQuery()->getSingleScalarResult();

        $defaultValues = array_merge(
            [
                'Id'        => $zedSupplierHighestId + 1,
                'CreatedAt' => $date,
                'UpdatedAt' => $date,
                'Type'      => 2,
                'Name'      => 'Name #' . uniqid(),
                'Cnpj'      => 'Cnpj #' . uniqid(),
                'Phone'     => 'Phone #' . uniqid(),
                'Address1'  => 'Address1 #' . uniqid(),
                'Address2'  => 'Address2 #' . uniqid(),
                'Address3'  => 'Address3 #' . uniqid(),
                'zipcode'   => 'zipcode'
            ],
            $values
        );

        $zedSupplier = new ZedSupplier();

        foreach ($defaultValues as $method => $param) {
            $setMethod = "set$method";

            if (method_exists($zedSupplier, $setMethod)) {
                $zedSupplier->$setMethod($param);
            }
        }

        self::$entityManager->persist($zedSupplier);
        self::$entityManager->flush();

        return $zedSupplier;
    }

    /**
     * @param array $values
     *
     * @return ZedOrder
     */
    protected function zedOrderFactory($values = array())
    {
        $date = new \DateTime();
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository        = self::$entityManager->getRepository('NatueZedBundle:ZedOrder');
        $queryBuilder      = $repository->createQueryBuilder('zedOrder')
            ->select('MAX(zedOrder.id)');
        $zedOrderHighestId = $queryBuilder->getQuery()->getSingleScalarResult();

        $defaultValues = array_merge(
            [
                'Id'          => $zedOrderHighestId + 1,
                'CreatedAt'   => $date,
                'UpdatedAt'   => $date,
                'IncrementId' => 'IncrementId #' . uniqid(),
            ],
            $values
        );

        $zedOrder = new ZedOrder();

        foreach ($defaultValues as $method => $param) {
            $setMethod = "set$method";

            if (method_exists($zedOrder, $setMethod)) {
                $zedOrder->$setMethod($param);
            }
        }

        self::$entityManager->persist($zedOrder);
        self::$entityManager->flush();

        return $zedOrder;
    }

    /**
     * @param array $values
     *
     * @return ZedOrderItem
     */
    protected function zedOrderItemFactory($values = array())
    {
        $date = new \DateTime();
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository            = self::$entityManager->getRepository('NatueZedBundle:ZedOrderItem');
        $queryBuilder          = $repository->createQueryBuilder('zedOrderItem')
            ->select('MAX(zedOrderItem.id)');
        $zedOrderItemHighestId = $queryBuilder->getQuery()->getSingleScalarResult();

        $defaultValues = array_merge(
            [
                'Id'                 => $zedOrderItemHighestId + 1,
                'CreatedAt'          => $date,
                'UpdatedAt'          => $date,
                'ZedProduct'         => $this->zedProductFactory(),
                'ZedOrderItemStatus' => $this->zedOrderItemStatusFactory(),
                'ZedOrder'           => $this->zedOrderFactory()
            ],
            $values
        );

        $zedSupplier = new ZedOrderItem();

        foreach ($defaultValues as $method => $param) {
            $setMethod = "set$method";

            if (method_exists($zedSupplier, $setMethod)) {
                $zedSupplier->$setMethod($param);
            }
        }

        self::$entityManager->persist($zedSupplier);
        self::$entityManager->flush();

        return $zedSupplier;
    }

    /**
     * @param array $values
     *
     * @return ZedOrderItemStatus
     */
    protected function zedOrderItemStatusFactory($values = array())
    {
        $date = new \DateTime();
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository                  = self::$entityManager->getRepository('NatueZedBundle:ZedOrderItemStatus');
        $queryBuilder                = $repository->createQueryBuilder('zedOrderItemStatus')
            ->select('MAX(zedOrderItemStatus.id)');
        $zedOrderItemStatusHighestId = $queryBuilder->getQuery()->getSingleScalarResult();

        $defaultValues = array_merge(
            [
                'Id'        => $zedOrderItemStatusHighestId + 1,
                'CreatedAt' => $date,
                'UpdatedAt' => $date,
                'Name'      => 'Name #' . uniqid(),
            ],
            $values
        );

        $zedOrderItemStatus = new ZedOrderItemStatus();

        foreach ($defaultValues as $method => $param) {
            $setMethod = "set$method";

            if (method_exists($zedOrderItemStatus, $setMethod)) {
                $zedOrderItemStatus->$setMethod($param);
            }
        }

        self::$entityManager->persist($zedOrderItemStatus);
        self::$entityManager->flush();

        return $zedOrderItemStatus;
    }
}
