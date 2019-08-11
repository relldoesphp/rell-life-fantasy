<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/11/19
 * Time: 2:00 AM
 */

namespace Player\Model\Stats\Sql;

use InvalidArgumentException;
use Player\Model\Player\SqlCommands\SqlQBCommand;
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
use Player\Model\Stats\GameLog;
use Player\Model\Stats\SeasonStats;
use Player\Model\Stats\StatsCommandInterface;

class SqlStatsCommand implements StatsCommandInterface
{

    private $db;
    private $consoleAdapter;

    public function __construct(AdapterInterface $db, Console $consoleAdapter)
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
    }

    /**
     * @param GameLog $gameLog
     * @return mixed
     */
    public function saveGameLog(GameLog $gameLog)
    {
        // TODO: Implement saveGameLog() method.
    }

    /**
     * @param GameLog $gameLog
     * @return mixed
     */
    public function createGameLog(GameLog $gameLog)
    {
        // TODO: Implement createGameLog() method.
    }

    /**
     * @param GameLog $gameLog
     * @return mixed
     */
    public function updateGameLog(GameLog $gameLog)
    {
        // TODO: Implement updateGameLog() method.
    }

    /**
     * @param GameLog $gameLog
     * @return mixed
     */
    public function deleteGameLog(GameLog $gameLog)
    {
        // TODO: Implement deleteGameLog() method.
    }

    /**
     * @param SeasonStats $seasonStat
     * @return mixed
     */
    public function saveSeasonStat(SeasonStats $seasonStat)
    {
        // TODO: Implement saveSeasonStat() method.
    }

    /**
     * @param SeasonStats $seasonStat
     * @return mixed
     */
    public function createSeasonStat(SeasonStats $seasonStat)
    {
        // TODO: Implement createSeasonStat() method.
    }

    /**
     * @param SeasonStats $seasonStat
     * @return mixed
     */
    public function updateSeasonStat(SeasonStats $seasonStat)
    {
        // TODO: Implement updateSeasonStat() method.
    }

    /**
     * @param SeasonStats $seasonStat
     * @return mixed
     */
    public function deleteSeasonStat(SeasonStats $seasonStat)
    {
        // TODO: Implement deleteSeasonStat() method.
    }
}