<?php

namespace Natue\Bundle\UserBundle\Tests\Controller;

use Natue\Bundle\CoreBundle\Tests\WebTestCase;
use Natue\Bundle\UserBundle\Service\Roles;

/**
 * Roles test
 */
class RolesTest extends WebTestCase
{

    /**
     * @var array
     */
    private static $rolesArray = [
        'role' => [
            'admin'       => null,
            'super_admin' => null,
            'user'        => [
                'group' => [
                    'create' => null,
                    'read'   => null,
                    'update' => null,
                    'delete' => null,
                ],
                'user'  => [
                    'create' => null,
                    'read'   => null,
                    'update' => null,
                    'delete' => null,
                ]
            ]
        ]
    ];

    /**
     * @return void
     */
    public function testConstructor()
    {
        $roles = $this->getMockBuilder(
            'Natue\Bundle\UserBundle\Service\Roles'
        )
            ->setConstructorArgs([self::$rolesArray])
            ->getMock();

        $this->assertAttributeEquals(self::$rolesArray, 'roles', $roles);
    }

    /**
     * @return void
     */
    public function testGetFormatedRoles()
    {
        $roles = $this->getMockBuilder(
            'Natue\Bundle\UserBundle\Service\Roles'
        )
            ->setConstructorArgs([self::$rolesArray])
            ->setMethods(['formatRoles'])
            ->getMock();

        $roles->expects($this->once())
            ->method('formatRoles')
            ->will($this->returnValue(1));

        $this->assertEquals(1, $roles->getFormatedRoles());
    }

    /**
     * @return void
     */
    public function testFormatRoles()
    {
        $rolesResultExpected = [
            'ROLE_ADMIN'             => 'ROLE_ADMIN',
            'ROLE_SUPER_ADMIN'       => 'ROLE_SUPER_ADMIN',
            'ROLE_USER_GROUP_CREATE' => 'ROLE_USER_GROUP_CREATE',
            'ROLE_USER_GROUP_READ'   => 'ROLE_USER_GROUP_READ',
            'ROLE_USER_GROUP_UPDATE' => 'ROLE_USER_GROUP_UPDATE',
            'ROLE_USER_GROUP_DELETE' => 'ROLE_USER_GROUP_DELETE',
            'ROLE_USER_USER_CREATE'  => 'ROLE_USER_USER_CREATE',
            'ROLE_USER_USER_READ'    => 'ROLE_USER_USER_READ',
            'ROLE_USER_USER_UPDATE'  => 'ROLE_USER_USER_UPDATE',
            'ROLE_USER_USER_DELETE'  => 'ROLE_USER_USER_DELETE',
        ];

        $roles = new Roles(self::$rolesArray);

        $formatRolesMethod = $this->invokeMethod($roles, 'formatRoles', [self::$rolesArray]);
        $this->assertEquals($rolesResultExpected, $formatRolesMethod);
    }
}
