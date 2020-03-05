<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 4:40 PM
 */

namespace Player\Service;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ProgressBar\Adapter\Console;
use Player\Model\Matchup\MatchupCommandInterface;
use Player\Model\Matchup\MatchupRepositoryInterface;
use Player\Model\Stats\StatsCommandInterface;
use Player\Model\Stats\StatsRepositoryInterface;
use Player\Model\Team\TeamRepositoryInterface;

class MatchupManager
{

    private $db;
    private $consoleAdapter;
    private $matchupCommand;
    private $matchupRepository;
    private $statsCommand;
    private $statsRepository;
    private $teamRepository;

    public function __construct(
        AdapterInterface $db,
        Console $consoleAdapter,
        MatchupCommandInterface $matchupCommand,
        MatchupRepositoryInterface $matchupRepository,
        StatsCommandInterface $statsCommand,
        StatsRepositoryInterface $statsRepository,
        TeamRepositoryInterface $teamRepository
    )
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
        $this->matchupRepository = $matchupRepository;
        $this->matchupCommand = $matchupCommand;
        $this->statsCommand = $statsCommand;
        $this->statsRepository = $statsRepository;
        $this->teamRepository = $teamRepository;
    }

    public function getMatchups($week, $year) {
        $matchups = $this->matchupRepository->getMatchupsByWeekYear($week, $year);
        return $matchups;
    }

    public function getMatchupById($id) {
        $matchup = $this->matchupRepository->getMatchupById($id);
        return $matchup;
    }

}