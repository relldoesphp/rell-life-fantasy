<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/22/19
 * Time: 8:01 PM
 */

namespace User\Model\User;


interface RepositoryInterface
{
    public function getUserById($id);

    public function getUserByEmail($email);

    public function getAllUsers();
}