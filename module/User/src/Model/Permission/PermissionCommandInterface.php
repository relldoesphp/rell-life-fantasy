<?php

namespace User\Model\Permission;

interface PermissionCommandInterface
{
    public function addPermission(Permission $user);

    public function updatePermission(Permission $user);

    public function deletePermission(Permission $user);
}