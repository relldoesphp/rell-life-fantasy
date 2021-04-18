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
use Player\Model\Matchup\Matchup;
use Player\Model\Matchup\MatchupCommandInterface;
use Player\Model\Matchup\MatchupRepositoryInterface;
use Player\Model\Stats\StatsCommandInterface;
use Player\Model\Stats\StatsRepositoryInterface;
use Player\Model\Team\TeamRepositoryInterface;
use Player\Service\SportsInfoApi;

class MatchupManager
{

    private $db;
    private $consoleAdapter;
    private $matchupCommand;
    private $matchupRepository;
    private $statsCommand;
    private $statsRepository;
    private $teamRepository;
    private $sportsInfoApi;

    public function __construct(
        AdapterInterface $db,
        Console $consoleAdapter,
        MatchupCommandInterface $matchupCommand,
        MatchupRepositoryInterface $matchupRepository,
        StatsCommandInterface $statsCommand,
        StatsRepositoryInterface $statsRepository,
        TeamRepositoryInterface $teamRepository,
        SportsInfoApi $sportsInfoApi
    )
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
        $this->matchupRepository = $matchupRepository;
        $this->matchupCommand = $matchupCommand;
        $this->statsCommand = $statsCommand;
        $this->statsRepository = $statsRepository;
        $this->teamRepository = $teamRepository;
        $this->sportsInfoApi = $sportsInfoApi;
    }

    public function getMatchups($week, $year) {
        $matchups = $this->matchupRepository->getMatchupsByWeekYear($week, $year);
        return $matchups;
    }

    public function getMatchupById($id) {
        $matchup = $this->matchupRepository->getMatchupById($id);
        return $matchup;
    }

    public function importGames(){
        $games = $this->sportsInfoApi->getSchedule('2020');
        foreach ($games as $game){
            $matchup = new Matchup();
            $matchup->setYear($game['season']);
            $matchup->setWeek($game['week']);
            $matchup->setAway($game['awayTeamId']);
            $matchup->setHome($game['homeTeamId']);
            $matchup->setDate($game['gameDate']);
            $matchup->setGameId($game['gameId']);
            $this->matchupCommand->saveMatchup($matchup);
        }
    }

}