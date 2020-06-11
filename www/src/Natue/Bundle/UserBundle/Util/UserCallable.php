<?php

namespace Natue\Bundle\UserBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Natue\Bundle\UserBundle\Common\UserCallableInterface;

class UserCallable implements UserCallableInterface
{
    /** @var ContainerInterface * */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @{inheritdoc}
     */
    public function getCurrentUser()
    {
        if (!$this->container->get('security.context')->getToken()) {
            return false;
        }

        return $this->container->get('security.context')->getToken()->getUser() ?: false;
    }
}
