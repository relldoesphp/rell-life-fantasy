<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 12:58 PM
 */

namespace Player\Model\Matchup\Sql;

use Player\Model\Matchup\Matchup;
use Player\Model\Matchup\MatchupCommandInterface;
use Zend\ProgressBar\Adapter\Console;
use Zend\Db\Adapter\AdapterInterface;

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
        // TODO: Implement saveMatchup() method.
    }

    /**
     * @param \Player\Model\Matchup\Matchup $matchup
     * @return mixed
     */
    public function createMatchup(Matchup $matchup)
    {
        // TODO: Implement createMatchup() method.
    }

    /**
     * @param \Player\Model\Matchup\Matchup $matchup
     * @return mixed
     */
    public function updateMatchup(Matchup $matchup)
    {
        // TODO: Implement updateMatchup() method.
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