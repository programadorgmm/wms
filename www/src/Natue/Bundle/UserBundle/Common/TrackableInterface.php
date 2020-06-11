<?php

namespace Natue\Bundle\UserBundle\Common;

use Symfony\Component\Security\Core\User\UserInterface;

interface TrackableInterface
{
    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user);
}
