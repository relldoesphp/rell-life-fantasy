<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 4:44 PM
 */

namespace Player\Model\Player\Sql;

use InvalidArgumentException;
use RuntimeException;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Predicate;
use Player\Model\Player\Player;
use Player\Model\Stats\SeasonStats;
use Player\Model\Player\PlayerRepositoryInterface;

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
            'full_name' => new Expression("concat(first_name,' ',last_name,' ',position,' ',team)"),
            "nohash" => new Expression("Replace(json_unquote(player_info->'$.hashtag'),'#','')"),
            'position'
        ]);

        if (empty($type)) {
            $select->where->in('position', ['WR','TE','RB','OB']);
        } else {
            $select->where(["position = ?" => $type]);
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
        $query = str_replace(' ', '', $query);
        $sql    = new Sql($this->db);
        $select = $sql->select('player_test')->columns([
            'id',
            'full_name' => new Expression("concat(first_name,' ',last_name,' ',position,' ',team)"),
            "nohash" => new Expression("Replace(json_unquote(player_info->'$.hashtag'),'#','')"),
            'position'
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

        $select = $sql->select('season_stats');
        $select->where(['sleeper_id = ?' => $player->getSleeperId()]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $player->seasonStats = $resultSet->toArray();

        $select = $sql->select('game_logs');
        $select->where(['sleeper_id = ?' => $player->getSleeperId()]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $player->gameLogs = $resultSet->toArray();

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

        $select = $sql->select('season_stats');
        $select->where(['sleeper_id = ?' => $player->getSleeperId()]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $player->seasonStats = $resultSet->toArray();
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

    public function getSeasonStats($sleeperId)
    {
        // TODO: Implement getSeasonStats() method.
        $sql    = new Sql($this->db);
        $select = $sql->select('season_stats');
        $select->where(['sleeper_id = ?' => $sleeperId]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, SeasonStats::class);
        $resultSet->initialize($result);
        return $resultSet->toArray();
    }
}