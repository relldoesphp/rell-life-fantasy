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
    private $db;

    private $hydrator;

    private $gameLogPrototype;

    private $seasonStatPrototype;


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

    /**
     * @param $id
     * @return mixed
     */
    public function getGameLogsById($id)
    {
        // TODO: Implement getGameLogsById() method.
    }

    /**
     * @param $sleeperId
     * @return mixed
     */
    public function getGameLogsBySleeperId($sleeperId)
    {
        // TODO: Implement getGameLogsBySleeperId() method.
    }

    /**
     * @param $year
     * @param $position
     * @param $types
     * @return mixed
     */
    public function makeSeasonRanks($year, $position, $types)
    {
        // TODO: Implement makeSeasonRanks() method.
    }

    /**
     * @param $week
     * @param $position
     * @param $types
     * @return mixed
     */
    public function makeWeeklyRanks($week, $position, $types)
    {
        // TODO: Implement makeWeeklyRanks() method.
    }

}