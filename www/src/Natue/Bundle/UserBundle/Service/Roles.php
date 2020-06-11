<?php

namespace Natue\Bundle\UserBundle\Service;

/**
 * Roles service. Get all available roles
 */
class Roles
{
    /**
     * @var array
     */
    protected $roles;

    /**
     * @param array $roles Roles from roles.yml
     */
    public function __construct($roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function getFormatedRoles()
    {
        return $this->formatRoles($this->roles);
    }

    /**
     * @param array  $roles  Roles on array
     * @param string $prefix Prefix fro role name
     *
     * @return array
     */
    protected function formatRoles($roles, $prefix = null)
    {
        $formatedRoles = [];

        foreach ($roles as $role => $roleValue) {
            if (is_array($roleValue)) {
                $return        = $this->formatRoles($roleValue, $prefix . $role . '_');
                $formatedRoles = array_merge($formatedRoles, $return);
            } else {
                $formatedRole                 = strtoupper($prefix . $role);
                $formatedRoles[$formatedRole] = $formatedRole;
            }
        }

        return $formatedRoles;
    }
}
