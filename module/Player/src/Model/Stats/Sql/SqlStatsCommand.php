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
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\ProgressBar\ProgressBar;
use Laminas\ProgressBar\Adapter\Console;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Http\Request;
use Laminas\Http\Client;
use Laminas\Dom\Query;
use Player\Model\Stats\GameLog;
use Player\Model\Stats\SeasonStats;
use Player\Model\Stats\StatsCommandInterface;

class SqlStatsCommand implements StatsCommandInterface
{

    private $gameLogTable = 'game_log';
    private $seasonStatTable = 'season_stat';

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
        if ($gameLog->getId() != null) {
            $this->updateGameLog($gameLog);
        } else {
            $this->createGameLog($gameLog);
        }
    }

    /**
     * @param GameLog $gameLog
     * @return mixed
     */
    public function createGameLog(GameLog $gameLog)
    {
        $gameLog->encodeJson();

        /** Insert new player **/
        $sql    = new Sql($this->db);
        $insert = $sql->insert($this->gameLogTable);
        $insert->values([
            'player_id' => $gameLog->getPlayerId(),
            'sleeper_id' => $gameLog->getSleeperId(),
            'year' => $gameLog->getYear(),
            'week' => $gameLog->getWeek(),
            'stats' => $gameLog->getStats(),
            'ranks' => $gameLog->getRanks(),
            'notes' => $gameLog->getNotes(),
            'home' => $gameLog->getHome(),
            'away' => $gameLog->getAway()
        ]);

        $stmt = $sql->prepareStatementForSqlObject($insert);
        $result = $stmt->execute();
        return $result;
    }

    /**
     * @param GameLog $gameLog
     * @return mixed
     */
    public function updateGameLog(GameLog $gameLog)
    {
        $gameLog->encodeJson();

        /** Update player **/
        $sql    = new Sql($this->db);
        $update = $sql->update($this->gameLogTable);
        $update->set([
            'player_id' => $gameLog->getPlayerId(),
            'sleeper_id' => $gameLog->getSleeperId(),
            'year' => $gameLog->getYear(),
            'week' => $gameLog->getWeek(),
            'stats' => $gameLog->getStats(),
            'ranks' => $gameLog->getRanks(),
            'notes' => $gameLog->getNotes(),
            'home' => $gameLog->getHome(),
            'away' => $gameLog->getAway()
        ]);
        $update->where(['id = ?' => $gameLog->getId()]);
        $stmt   = $sql->prepareStatementForSqlObject($update);
        try {
            $result = $stmt->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
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
        if ($seasonStat->getId() != null) {
            $this->updateSeasonStat($seasonStat);
        } else {
            $this->createSeasonStat($seasonStat);
        }
    }

    /**
     * @param SeasonStats $seasonStat
     * @return mixed
     */
    public function createSeasonStat(SeasonStats $seasonStat)
    {
        $seasonStat->encodeJson();

        /** Insert new player **/
        $sql    = new Sql($this->db);
        $insert = $sql->insert($this->seasonStatTable);
        $insert->values([
            'player_id' => $seasonStat->getPlayerId(),
            'sleeper_id' => $seasonStat->getSleeperId(),
            'year' => $seasonStat->getYear(),
            'stats' => $seasonStat->getStats(),
            'ranks' => $seasonStat->getRanks(),
            'notes' => $seasonStat->getNotes()
        ]);

        $stmt = $sql->prepareStatementForSqlObject($insert);
        try {
            $result = $stmt->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param SeasonStats $seasonStat
     * @return mixed
     */
    public function updateSeasonStat(SeasonStats $seasonStat)
    {
        $seasonStat->encodeJson();

        /** Insert new season_stat **/
        $sql    = new Sql($this->db);
        $update = $sql->update($this->seasonStatTable);
        $update->set([
            'player_id' => $seasonStat->getPlayerId(),
            'sleeper_id' => $seasonStat->getSleeperId(),
            'year' => $seasonStat->getYear(),
            'stats' => $seasonStat->getStats(),
            'ranks' => $seasonStat->getRanks(),
            'notes' => $seasonStat->getNotes()
        ]);
        $update->where(['id = ?' => $seasonStat->getId()]);
        $stmt = $sql->prepareStatementForSqlObject($update);
        try {
            $result = $stmt->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
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