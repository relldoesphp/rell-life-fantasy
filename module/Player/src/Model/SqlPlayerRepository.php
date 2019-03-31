<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 4:44 PM
 */

namespace Player\Model;

use InvalidArgumentException;
use RuntimeException;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Hydrator\HydratorInterface;
use Player\Model\Player;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\HydratingResultSet;

class SqlPlayerRepository implements PlayerRepositoryInterface
{
    /**
     * @var AdapterInterface
     */
    private $db;

    private $hydrator;

    private $playerPrototype;

    /**
     * ZendDbPlayerRespository constructor.
     */
    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        Player $playerPrototype)
    {
        $this->db = $db;
        $this->hydrator = $hydrator;
        $this->playerPrototype = $playerPrototype;
    }

    /**
     * @return mixed
     */
    public function getPlayerNames()
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('players')->columns([
            'id',
            'firstName',
            'lastName',
            'alias',
            'position',
            'team',
            'fullName' => new Expression('CONCAT(firstName," ",lastName)')
        ]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        return  $resultSet->toArray();
    }

    /**
     * @return mixed
     */
    public function findAllPlayers()
    {
        // TODO: Implement findAllPlayers() method.
    }

    public function findPlayerByAlias($alias){
        $sql    = new Sql($this->db);
        $select = $sql->select('players');
        $select->where(['alias = ?' => $alias]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->playerPrototype);
        $resultSet->initialize($result);
        $player = $resultSet->current();

        return $player;
    }

    /**
     * @return mixed
     */
    public function findPlayer($id)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('players');
        $select->where(['id = ?' => $id]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->playerPrototype);
        $resultSet->initialize($result);
        $player = $resultSet->current();

        return $player;
    }

    /**
     * @return mixed
     */
    public function getPlayerMetrics($id, $position)
    {
        $table = strtolower($position).'_metrics';
        $sql    = new Sql($this->db);
        $select = $sql->select($table);
        $select->where(['playerId = ?' => $id]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        return $resultSet->toArray();
    }

    /**
     * @return mixed
     */
    public function getPlayerPercentiles($id, $position)
    {
        $table = strtolower($position).'_percentiles';
        $sql    = new Sql($this->db);
        $select = $sql->select($table);
        $select->where(['playerId = ?' => $id]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        return $resultSet->toArray();
    }



}