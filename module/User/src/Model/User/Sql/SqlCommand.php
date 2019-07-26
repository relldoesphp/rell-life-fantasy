<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/22/19
 * Time: 8:03 PM
 */

namespace User\Model\User\Sql;

use User\Model\User\CommandInterface;
use User\Model\User\User;

class SqlCommand implements CommandInterface
{
    private $db;

    public function __construct($db){
        $this->db = $db;
    }
    /**
     * @param User $user
     * @return mixed
     */
    public function addUser(User $user)
    {
        // TODO: Implement addUser() method.
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function updateUser(User $user)
    {
        // TODO: Implement updateUser() method.
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function deleteUser(User $user)
    {
        // TODO: Implement deleteUser() method.
    }

}