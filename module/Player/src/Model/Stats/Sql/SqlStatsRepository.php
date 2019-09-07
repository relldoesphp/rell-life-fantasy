<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/11/19
 * Time: 1:53 AM
 */

namespace Player\Model\Stats\Sql;

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
use Player\Model\Stats\GameLog;
use Player\Model\Stats\SeasonStats;
use Player\Model\Stats\StatsRepositoryInterface;

class SqlStatsRepository implements StatsRepositoryInterface
{
    private $gameLogTable = 'game_log';
    private $seasonStatTable = 'season_stat';

    private $db;

    private $hydrator;

    private $gameLogPrototype;

    private $seasonStatPrototype;

    public $ranks = [
        "QB" => [
            'pass_att',
            'pass_cmp',
            'pass_fd',
            'pass_td',
            'pass_int',
            'pass_yd',
            'pass_ypa',
            'pass_ypc',
            'pass_sack',
            'cmp_pct_avg',
            'off_snp_avg',
            'pass_rtg_avg',
            'pass_fd_avg',
            'rush_att',
            'rush_yd',
            'rush_ypa',
            'rush_att_avg',
            'rush_yd_avg',
            'rush_fd_avg',
            'snp_pct',
            'pts_ppr_avg',
            'pts_half_ppr_avg',
            'pts_std_avg',
            'snp_pct',
        ],
        "WR" => [
            'pts_ppr',
            'pts_half_ppr',
            'pts_std',
            'rec_tgt',
            'rec',
            'rec_yd',
            'rec_td',
            'rec_ypt',
            'rec_ypr',
            'rec_fd',
            'rush_att',
            'rush_yd',
            'rush_ypa',
            'pts_ppr_avg',
            'pts_half_ppr_avg',
            'pts_std_avg',
            'tgt_avg',
            'rec_avg',
            'rec_yd_avg',
            'rec_fd_avg',
            'rush_att_avg',
            'rush_yd_avg',
            'rush_fd_avg',
            'snp_pct',
            'opp_per_snap'
        ],
        "TE" => [
            'pts_ppr',
            'pts_half_ppr',
            'pts_std',
            'rec_tgt',
            'rec',
            'rec_yd',
            'rec_td',
            'rec_ypt',
            'rec_ypr',
            'rec_fd',
            'rush_att',
            'rush_yd',
            'rush_ypa',
            'pts_ppr_avg',
            'pts_half_ppr_avg',
            'pts_std_avg',
            'tgt_avg',
            'rec_avg',
            'rec_yd_avg',
            'rec_fd_avg',
            'opp_per_snap',
            'snap_pct',
            'opp_per_snap'
        ],
        "RB" => [
            'pts_ppr',
            'pts_half_ppr',
            'pts_std',
            'rec_tgt',
            'rec',
            'rec_yd',
            'rec_td',
            'rec_ypt',
            'rec_ypr',
            'rec_fd',
            'rush_att',
            'rush_yd',
            'rush_ypa',
            'pts_ppr_avg',
            'pts_half_ppr_avg',
            'pts_std_avg',
            'tgt_avg',
            'rec_avg',
            'rec_yd_avg',
            'rec_fd_avg',
            'rush_att_avg',
            'rush_yd_avg',
            'rush_fd_avg',
            'opp_per_snap',
            'snap_pct',
        ]
    ];


    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        GameLog $gameLogPrototype,
        SeasonStats $seasonStatPrototype
    )
    {
        $this->db = $db;
        $this->hydrator = $hydrator;
        $this->gameLogPrototype = $gameLogPrototype;
        $this->seasonStatPrototype = $seasonStatPrototype;
    }

    public function getGameLogsByPosition($position, $year, $week)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select(['gl' => $this->gameLogTable]);
        $select->join(['p' => 'player_test'],"gl.sleeper_id = p.sleeper_id", ["position"]);
        $select->where([
            "p.position = ?" => $position,
            "gl.year = ?" => $year,
            "gl.week = ?" => $week
        ]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->gameLogPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    public function getSeasonStatsByPosition($position, $year)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select(['ss' => $this->seasonStatTable]);
        $select->join(['p' => 'player_test'],"ss.sleeper_id = p.sleeper_id", ["position"], $select::JOIN_OUTER);
        $select->where([
            "p.position = ?" => $position,
            "ss.year = ?" => $year,
        ]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->seasonStatPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getGameLogsById($id)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select($this->gameLogTable);
        $select->where(["id = ?" => $id]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->gameLogPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getGameLogsByWeekYearSleeper($week, $year, $sleeperId)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select($this->gameLogTable);
        $select->where([
            "week = ?" => $week,
            "year = ?" => $year,
            "sleeper_id = ?" => $sleeperId
        ]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->gameLogPrototype);
        $resultSet->initialize($result);
        return $resultSet->current();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getGameLogsByWeekYearPosition($week, $year, $position)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select($this->gameLogTable);
        $select->where([
            "week = ?" => $week,
            "year = ?" => $year,
            "position = ?" => $position
        ]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->gameLogPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    /**
     * @param $sleeperId
     * @return mixed
     */
    public function getGameLogsBySleeperId($sleeperId)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select($this->gameLogTable);
        $select->where(["sleeper_id = ?" => $sleeperId]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->gameLogPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    public function getGameLogsByPlayerId($playerId)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select($this->gameLogTable);
        $select->where(["player_id = ?" => $playerId]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->gameLogPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }


    /**
     * @param $id
     * @return mixed
     */
    public function getSeasonStatsById($id)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select($this->seasonStatTable);
        $select->where(["id = ?" => $id]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->seasonStatPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    /**
     * @param $sleeperId
     * @return mixed
     */
    public function getSeasonStatsByWhere($where)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select($this->seasonStatTable);
        $select->where($where);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->seasonStatPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    /**
     * @param $sleeperId
     * @return mixed
     */
    public function getSeasonStatsBySleeperId($sleeperId)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select($this->seasonStatTable);
        $select->where(["sleeper_id = ?" => $sleeperId]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->seasonStatPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    public function getSeasonStatsByPlayerId($playerId)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select($this->seasonStatTable);
        $select->where(["player_id = ?" => $playerId]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->seasonStatPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    /**
     * @param $year
     * @param $position
     * @param $types
     * @return mixed
     */
    public function makeSeasonRanks($year, $position)
    {
        $ranks = [];
        $rankArrays = $this->ranks;
        foreach ($rankArrays[$position] as $stat) {

            $sql = <<<EOT
Select ss.id, rank() over (ORDER BY lpad(format(json_unquote(stats->'$.{$stat}'),2),7,'0') DESC) as ranking
from {$this->seasonStatTable} ss
join player_test p on (p.sleeper_id = ss.sleeper_id)
where
  ss.year = {$year} and p.position = "{$position}" and json_unquote(stats->'$.{$stat}') != "null"
EOT;

            $stmt = $this->db->query($sql);
            $result = $stmt->execute();
            if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
                return [];
            }

            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            $ranks[$stat] = [];
            foreach($resultSet as $row) {
                $ranks[$stat][$row->id] = $row->ranking;
            }
        }
           return $ranks;
    }

    /**
     * @param $week
     * @param $position
     * @param $types
     * @return mixed
     */
    public function makeWeeklyRanks($week, $year, $position)
    {
        $ranks = [];
        $rankArrays = $this->ranks;
        foreach ($rankArrays[$position] as $stat) {

            $sql = <<<EOT
Select gl.id, rank() over (ORDER BY lpad(format(json_unquote(stats->'$.{$stat}'),4),7,'0') DESC) ranking
from {$this->gameLogTable} gl
join player_test p on (p.sleeper_id = gl.sleeper_id)
where
  gl.week = "{$week}" and gl.year = "{$year}" and p.position = "{$position}" and json_unquote(stats->'$.{$stat}') != "null"
EOT;

            $stmt = $this->db->query($sql);
            $result = $stmt->execute();
            if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
                return [];
            }

            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            $ranks[$stat] = [];
            foreach($resultSet as $row) {
                $ranks[$stat][$row->id] = $row->ranking;
            }
        }
        return $ranks;
    }

}