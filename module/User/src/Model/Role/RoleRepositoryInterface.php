<?php


namespace User\Model\Role;

use User\Model\User\User;

interface RoleRepositoryInterface
{
    public function findAllRoles();

    public function findRoleById($id);

    public function findRolesForUser(User $user);
}