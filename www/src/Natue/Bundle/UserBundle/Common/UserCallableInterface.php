<?php

namespace Natue\Bundle\UserBundle\Common;

interface UserCallableInterface
{
    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getCurrentUser();
}
