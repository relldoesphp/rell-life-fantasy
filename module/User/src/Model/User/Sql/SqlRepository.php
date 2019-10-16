<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/22/19
 * Time: 8:04 PM
 */

namespace User\Model\User\Sql;

use User\Model\User\RepositoryInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\HydratingResultSet;

class SqlRepository implements RepositoryInterface
{
    private $db;
    private $hydrator;
    private $userPrototype;

    public function __construct($db, $hydrator, $userPrototype){
        $this->db = $db;
        $this->hydrator = $hydrator;
        $this->userPrototype = $userPrototype;
    }
    /**
     * @param int $id
     * @return mixed
     */
    public function getUserById($id)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('user');
        $select->where(["id = ?" => $id]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->userPrototype);
        $resultSet->initialize($result);
        return $resultSet->current();
    }

    /**
     * @param $email
     * @return mixed
     */
    public function getUserByEmail($email)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('user');
        $select->where(["email = ?" => $email]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->userPrototype);
        $resultSet->initialize($result);
        return $resultSet->current();
    }

    /**
     * @return mixed
     */
    public function getAllUsers()
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('user');
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->userPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }
}