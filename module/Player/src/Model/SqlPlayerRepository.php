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
use Zend\Db\Sql\Predicate;

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
    public function getPlayerNames($type = "")
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('player_test')->columns([
            'id',
            'full_name' => new Expression("concat(first_name,' ',last_name,' ',position)"),
            "nohash" => new Expression("Replace(json_unquote(player_info->'$.hashtag'),'#','')")
        ]);

        if (!empty($type)) {
            $select->where->in('position', ['WR','TE','RB','OB']);
        }

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        return  $resultSet->toArray();
    }

    public function queryPlayers($query)
    {
        // TODO: Implement queryPlayers() method.
        $sql    = new Sql($this->db);
        $select = $sql->select('player_test')->columns([
            'id',
            'full_name' => new Expression("concat(first_name,' ',last_name,' ',position)"),
            "nohash" => new Expression("Replace(json_unquote(player_info->'$.hashtag'),'#','')")
        ]);
        $select->where->like('first_name', $query."%")
            ->or->like('last_name', $query."%")
            ->or->like('search_full_name', $query."%");

        $select->order([
            new Expression("json_unquote(player_info->'$.active') DESC"),
            new Expression("json_unquote(team_info->'$.depth_chart_order') ASC"),
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
        $alias = "#{$alias}";
        $sql    = new Sql($this->db);
        $select = $sql->select('player_test');
        $select->where(["player_info->'$.hashtag' = ?" => $alias]);
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
        $select = $sql->select('player_test');
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

    public function getTeamScores($team, $position)
    {
        if ($team == null) {
            return null;
        }

        $sql    = new Sql($this->db);
        $select = $sql->select("team");
        switch ($position) {
            case "WR":
                $select->columns([
                    'slot_wr_score',
                    'deep_wr_score',
                    'alpha_wr_score'
                ]);
                break;
            case "TE":
                $select->columns([
                    'slot_te_score',
                ]);
                break;
            case "RB":
                $select->columns([
                    'grinder_rb_score',
                    'sattelite_rb_score',
                    'alpha_rb_score'
                ]);
                break;
            default:
        }

        $select->where(['name = ?' => $team]);
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