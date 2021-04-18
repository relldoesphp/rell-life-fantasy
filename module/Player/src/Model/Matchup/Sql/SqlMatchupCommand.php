<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 12:58 PM
 */

namespace Player\Model\Matchup\Sql;

use Laminas\Db\Sql\Sql;
use Player\Model\Matchup\Matchup;
use Player\Model\Matchup\MatchupCommandInterface;
use Laminas\ProgressBar\Adapter\Console;
use Laminas\Db\Adapter\AdapterInterface;

class SqlMatchupCommand implements MatchupCommandInterface
{

    private $db;
    private $consoleAdapter;

    public function __construct(AdapterInterface $db, Console $consoleAdapter)
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
    }

    /**
     * @param \Player\Model\Matchup\Matchup $matchup
     * @return mixed
     */
    public function saveMatchup(Matchup $matchup)
    {
        if ($matchup->getId() != null) {
            $this->updateMatchup($matchup);
        } else {
            $this->createMatchup($matchup);
        }
    }

    /**
     * @param \Player\Model\Matchup\Matchup $matchup
     * @return mixed
     */
    public function createMatchup(Matchup $matchup)
    {
        /** Insert new matchup **/
        $sql    = new Sql($this->db);
        $insert = $sql->insert('matchups');
        $insert->values([
            'year' => $matchup->getYear(),
            'week' => $matchup->getWeek(),
            'away' => $matchup->getAway(),
            'home' => $matchup->getHome(),
            'date' => $matchup->getDate(),
            "gameId" => $matchup->getGameId()
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
     * @param \Player\Model\Matchup\Matchup $matchup
     * @return mixed
     */
    public function updateMatchup(Matchup $matchup)
    {
        /** Insert new matchup **/
        $sql    = new Sql($this->db);
        $update = $sql->update('matchups');
        $update->set([
            'year' => $matchup->getYear(),
            'week' => $matchup->getWeek(),
            'away' => $matchup->getAway(),
            'home' => $matchup->getHome(),
            'date' => $matchup->getDate(),
            'gameId' => $matchup->getGameId()
        ]);
        $update->where(['id = ?' => $matchup->getId()]);
        $stmt = $sql->prepareStatementForSqlObject($update);
        try {
            $result = $stmt->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param \Player\Model\Matchup\Matchup $matchup
     * @return mixed
     */
    public function deleteMatchup(Matchup $matchup)
    {
        // TODO: Implement deleteMatchup() method.
    }

}