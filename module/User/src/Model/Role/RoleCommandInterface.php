<?php


namespace User\Model\Role;

interface RoleCommandInterface
{
    public function createRole(Role $role);

    public function updateRole(Role $role);

    public function deleteRole(Role $role);

}