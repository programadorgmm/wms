<?php

namespace Natue\Bundle\CoreBundle\EventListener;

use Doctrine\Common\EventArgs;

use Natue\Bundle\UserBundle\Common\UserCallableInterface;
use Natue\Bundle\UserBundle\Common\TrackableInterface;

class TrackUserListener
{
    /**
     * @var UserCallableInterface
     */
    protected $userCallable;

    /**
     * @param UserCallableInterface $userCallable
     **/
    public function __construct(UserCallableInterface $userCallable)
    {
        $this->userCallable = $userCallable;
    }

    /**
     * @param EventArgs $args
     **/
    public function prePersist(EventArgs $args)
    {
        $entity = $args->getEntity();

        if (!($entity instanceof TrackableInterface)) {
            return;
        }

        if (!($user = $this->userCallable->getCurrentUser())) {
            return;
        }

        $entity->setUser($user);
    }
}
