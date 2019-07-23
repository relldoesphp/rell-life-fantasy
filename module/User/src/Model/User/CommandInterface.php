<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/22/19
 * Time: 8:02 PM
 */

namespace User\Model\User;


interface CommandInterface
{
    public function addUser(User $user);

    public function updateUser(User $user);

    public function deleteUser(User $user);
}