<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/11/19
 * Time: 5:46 PM
 */

namespace Player\Model\Team\Sql;


use Player\Model\Team\TeamRepositoryInterface;

use InvalidArgumentException;
use RuntimeException;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\HydratingResultSet;
use Player\Model\Team\Team;

class SqlTeamRepository implements TeamRepositoryInterface
{
    /**
     * @var AdapterInterface
     */
    private $db;
    private $hydrator;
    private $teamPrototype;

    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        Team $teamPrototype)
    {
        $this->db = $db;
        $this->hydrator = $hydrator;
        $this->teamPrototype = $teamPrototype;
    }

    /**
     * @return mixed
     */
    public function getTeams()
    {
        // TODO: Implement getSeasonStats() method.
        $sql    = new Sql($this->db);
        $select = $sql->select('teams');
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->teamPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getTeamById($id)
    {
        // TODO: Implement getTeamById() method.
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getTeamByName($name)
    {
        // TODO: Implement getSeasonStats() method.
        $sql    = new Sql($this->db);
        $select = $sql->select('teams');
        $select->where(['team = ?' => $name]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->teamPrototype);
        $resultSet->initialize($result);
        return $resultSet->current();
    }

}