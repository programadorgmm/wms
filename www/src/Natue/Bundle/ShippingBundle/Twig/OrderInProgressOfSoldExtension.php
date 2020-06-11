<?php
namespace Natue\Bundle\ShippingBundle\Twig;

class OrderInProgressOfSoldExtension extends \Twig_Extension
{
    public function __construct($container)
    {
        $this->redis = $container->get('snc_redis.default');
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('order_in_progress_of_sold', array(
                $this, 'orderInProgressOfSold'
            )),
        );
    }

    public function orderInProgressOfSold($providerId)
    {
        return $this->redis->keys('expeditionOrders:'.$providerId.':*');
    }

    public function getName()
    {
        return 'order_in_progress_of_sold';
    }
}
