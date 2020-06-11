<?php

namespace Natue\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Natue\Bundle\UserBundle\Entity\User;

class UserData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->addUser('admin', $manager); // this will create reference 'user-admin' and etc.
        $this->addUser('test', $manager);
        $this->addUser('test2', $manager);
    }

    /**
     * This class has to be defined in order class
     *
     * @return bool|int
     */
    public function getOrder()
    {
        return \Natue\Bundle\CoreBundle\DataFixtures\Order::getOrder($this);
    }

    private function addUser($username, ObjectManager $manager)
    {
        $userAdmin = (new User())
            ->setUsername($username)
            // password is admin
            ->setPassword('oJDwE5rA7rTTPL+EYsU6aCM/DQeU6CJrB2tcisUbj4UD4NFjgeUGH8IpsL4R788gRT88dJcs6+vSGeO0KyPHDA==')
            ->setSalt('16bkvg6daiw0g40kw4sogkkw8go00c0')
            ->setUsernameCanonical($username)
            ->setEmail('unittests+'.$username.'@natue.com.br')
            ->setName($username)
            ->setSuperAdmin(true)
            ->setEnabled(true);

        $manager->persist($userAdmin);
        $manager->flush();

        $this->addReference('user-' . $username, $userAdmin);
    }
}
