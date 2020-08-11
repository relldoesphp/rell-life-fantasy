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
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\ResultSet\HydratingResultSet;
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
        $sql    = new Sql($this->db);
        $select = $sql->select('teams');
        $select->where(['id = ?' => $id]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->teamPrototype);
        $resultSet->initialize($result);
        return $resultSet->current();
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