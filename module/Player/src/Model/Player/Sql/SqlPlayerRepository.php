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
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Predicate;
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
        $select->where->nest()
            ->like('first_name', $query."%")
            ->or
            ->like('last_name', $query."%")
            ->or
            ->like('search_full_name', $query."%")
            ->unnest();
//            ->and
//            ->in("position", ["QB","RB","WR","TE"]);

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
    public function findAllPlayers($type = "")
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        if (!empty($type)) {
            switch ($type) {
                case "OL":
                    $select->where->in("position", ["C","G","OT"]);
                    break;
                case "DL":
                    $select->where->in("position", ['DT','NT','DE']);
                    break;
                default:
                    $select->where(["position = ?" => $type]);
            }
        }

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->playerPrototype);
        $resultSet->initialize($result);

        return $resultSet;
    }

    public function findAllPlayersNoCollege($type = "")
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        if (!empty($type)) {
            switch ($type) {
                case "OL":
                    $select->where->in("position", ["C","G","OT"]);
                    break;
                case "DL":
                    $select->where->in("position", ['DT','NT','DE']);
                    break;
                default:
                    $select->where([
                        "position = ?" => $type,
                        "team = ?" => "Rookie",
                        new Expression("json_unquote(college_stats)") => "[]"
                    ]);
            }
        }

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->playerPrototype);
        $resultSet->initialize($result);

        return $resultSet;
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

        if ($player == false) {
            return false;
        }

        $select = $sql->select('season_stat');
        $select->where(['sleeper_id = ?' => $player->getSleeperId()]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $player->seasonStats = $resultSet->toArray();

        $select = $sql->select('game_log');
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

        if (! $result instanceof ResultInterface || ! $result->isQueryResult() || $result->count() == 0) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->playerPrototype);
        $resultSet->initialize($result);
        try {
            $player = $resultSet->current();
        } catch (\Exception $e) {
            print_r($result);
            die();
        }


        $select = $sql->select('season_stats');
        $select->where(['sleeper_id = ?' => $player->getSleeperId()]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $player->seasonStats = $resultSet->toArray();
        return $player;
    }

    public function findPlayerByInfo($firstname, $lastname, $position)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('player_test');
        $select->where([
            'first_name = ?' => $firstname,
            'last_name = ?' => $lastname,
            'position = ?' => $position,
        ]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult() || $result->count() == 0) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->playerPrototype);
        $resultSet->initialize($result);
        try {
            $player = $resultSet->current();
        } catch (\Exception $e) {
            print_r($result);
            die();
        }


        $select = $sql->select('season_stats');
        $select->where(['sleeper_id = ?' => $player->getSleeperId()]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $player->seasonStats = $resultSet->toArray();
        return $player;
    }


    public function findPlayerBySleeperId($sleeperId)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where(['p.sleeper_id = ?' => $sleeperId]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->playerPrototype);
        $resultSet->initialize($result);
        return $resultSet->current();
    }


    public function findPlayerBySisId($sisId)
    {
        $sql = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where(['p.sis_id = ?' => $sisId]);
        $stmt = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (!$result instanceof ResultInterface || !$result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->playerPrototype);
        $resultSet->initialize($result);
        return $resultSet->current();
    }

    public function findPlayerByGsisId($gsisId)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where(["p.api_info->'$.gsis_id' = ?" => $gsisId]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->playerPrototype);
        $resultSet->initialize($result);
        return $resultSet->current();
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

    public function getPercentileRanks($type, $metrics = [])
    {
        switch ($type) {
            case "OL":
                $position = '"C","G","OT"';
                break;
            case "DL":
                $position = " 'DT','NT' ";
                break;
            default:
                $position = "'{$type}'";
        }

        $percentiles = [];
        foreach ($metrics as $name => $value) {

            $sql = <<<EOT
SELECT id, ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote({$value['field']}->'$.{$name}'),3),7,'0') {$value['sort']}),3) percentile_rank
FROM player_test
WHERE {$value['field']}->'$.{$name}' IS NOT NULL 
AND json_unquote({$value['field']}->'$.{$name}') != 'null' 
AND json_unquote({$value['field']}->'$.{$name}') != '0' 
AND json_unquote({$value['field']}->'$.{$name}') != '' 
AND json_unquote({$value['field']}->'$.{$name}') != '-' 
AND json_unquote({$value['field']}->'$.{$name}') != 'N/A' 
AND position in ({$position})
EOT;
            $stmt = $this->db->query($sql);
            $result = $stmt->execute();
            if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
                return [];
            }

            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            $percentiles[$name] = [];
            foreach($resultSet as $row) {
                $percentiles[$name][$row->id] = $row->percentile_rank;
            }
        }

        return $percentiles;
    }

    public function getPlayersByTeam($team)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where(['p.team = ?' => $team]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->playerPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    public function getCollegeTeammates($year, $college, $position)
    {
        // TODO: Implement getCollegeTeammates() method.
        $sql =<<<EOT
Select 
id,
first_name, 
last_name,
college_stats->'$."{$year}".dominator' as 'dominator',
college_stats->'$."{$year}".recTds' as 'recTds',
college_stats->'$."{$year}".recYds' as 'recYds'
from player_test
where player_info->'$.college' = '{$college}'
and position in ('{$position}')
and JSON_SEARCH(college_stats,'one','{$year}') is not null
EOT;
        $stmt = $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $resultArray = $resultSet->toArray();

        $teammates = [];
        foreach($resultArray as $row) {
            $teammates[$row['id']] = $row;
        }

        return $teammates;

    }

    public function findRookiesByName($firstname, $lastname, $position)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where([
            'p.team = ?' => 'Rookie',
            'p.first_name = ?' => $firstname,
            'p.last_name = ?' => $lastname,
            'p.position = ?' => $position
        ]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->playerPrototype);
        $resultSet->initialize($result);
        return $resultSet->current();
    }
}