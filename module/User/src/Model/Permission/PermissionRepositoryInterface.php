<?php


namespace User\Model\Permission;


interface PermissionRepositoryInterface
{
    public function getPermissionById($id);

    public function getAllPermissions();

    public function getPermissionByRole();
}