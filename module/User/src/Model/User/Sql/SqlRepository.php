<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/22/19
 * Time: 8:04 PM
 */

namespace User\Model\User\Sql;

use User\Model\User\RepositoryInterface;

class SqlRepository implements RepositoryInterface
{
    private $db;

    public function __construct($db){
        $this->db = $db;
    }
    /**
     * @param int $id
     * @return mixed
     */
    public function getUserById($id)
    {
        // TODO: Implement getUserById() method.
    }

    /**
     * @param $email
     * @return mixed
     */
    public function getUserByEmail($email)
    {
        // TODO: Implement getUserByEmail() method.
    }

    /**
     * @return mixed
     */
    public function getAllUsers()
    {
        // TODO: Implement getAllUser() method.
    }

}