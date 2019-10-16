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
use RuntimeException;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\ProgressBar\ProgressBar;
use Zend\ProgressBar\Adapter\Console;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Dom\Query;

class SqlCommand implements CommandInterface
{
    private $db;

    public function __construct($db){
        $this->db = $db;
    }

    public function save(User $user)
    {
        if ($user->getId() != null) {
            $this->updateUser($user);
        } else {
            $this->addUser($user);
        }
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function addUser(User $user)
    {
        /** Insert new user **/
        $sql    = new Sql($this->db);
        $insert = $sql->insert('user');
        $insert->values([
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'status' => $user->getStatus(),
            'date_created' => $user->getDateCreated()
        ]);

        $stmt = $sql->prepareStatementForSqlObject($insert);
        try {
            $result = $stmt->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function updateUser(User $user)
    {
        /** Insert new user **/
        $sql    = new Sql($this->db);
        $update = $sql->update('user');
        $update->set([
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'status' => $user->getStatus()
        ]);

        $update->where(['id = ?' => $user->getId()]);
        $stmt = $sql->prepareStatementForSqlObject($update);
        try {
            $result = $stmt->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function deleteUser(User $user)
    {
        $sql    = new Sql($this->db);
        $delete = $sql->delete('user');
        $delete->where(['id = ?' => $user->getId()]);
        $stmt = $sql->prepareStatementForSqlObject($delete);
        try {
            $result = $stmt->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}