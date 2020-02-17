<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/23/19
 * Time: 10:46 PM
 */

namespace Player\Service;

use Player\Model\Player\Player;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Json\Json;
use Zend\ProgressBar\Adapter\Console;
use Zend\Http\Request;
use Zend\Http\Client;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Player\Model\Stats\StatsCommandInterface;
use Player\Model\Stats\StatsRepositoryInterface;
use Zend\ProgressBar\ProgressBar;
use Player\Service\Position;


class PlayerManager
{
    private $db;
    private $consoleAdapter;
    private $playerCommand;
    private $playerRepository;
    private $statsCommand;
    private $statsRepository;

    public function __construct(
        AdapterInterface $db,
        Console $consoleAdapter,
        PlayerCommandInterface $playerCommand,
        PlayerRepositoryInterface $playerRepository,
        StatsCommandInterface $statsCommand,
        StatsRepositoryInterface $statsRepository
    )
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
        $this->playerRepository = $playerRepository;
        $this->playerCommand = $playerCommand;
        $this->statsCommand = $statsCommand;
        $this->statsRepository = $statsRepository;
    }

    public function getPlayer($id)
    {
        if (is_numeric($id)) {
            $player = $this->playerRepository->findPlayer($id);
        } else {
            $player = $this->playerRepository->findPlayerByAlias($id);
        }

        if ($player == false) {
            return false;
        }

        $player->decodeJson();

        $seasonStats = $this->statsRepository->getSeasonStatsBySleeperId($player->getSleeperId());
        if ($seasonStats !== false) {
            $stats = [];
            foreach ($seasonStats as $seasonStat) {
                $seasonStat->decodeJson();
                $stats[$seasonStat->year] = [
                    'stats' => $seasonStat->stats,
                    'ranks' => $seasonStat->ranks
                ];
            }
            $player->setSeasonStats($stats);
        }

        $gameLogs = $this->statsRepository->getGameLogsByPlayerId($player->getId());
        if ($gameLogs !== false && count($gameLogs) != 0) {
            foreach ($gameLogs as $gameLog) {
                $gameLog->decodeJson();
                $logs[] = $gameLog;
            }
            $player->setGameLogs($logs);
        } else {
            $player->setGameLogs([]);
        }

        return $player;
    }


    public function updateSleeperInfo()
    {
        $request = new Request();
        $uri = "https://api.sleeper.app/v1/players/nfl";
        $request->setUri($uri);

        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();

        $json = (array) json_decode($html);

        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($json));
        $pointer = 0;
        foreach ($json as $key => $value) {
            $sleeperId = $value->player_id;
            
            $player = $this->playerRepository->findPlayerBySleeperId($sleeperId);
            if (empty($player)) {
                $player = new Player();
                $player->setSleeperId($value->player_id);
            }

            if ($player->getSleeperId() == null) {
                $player->setSleeperId($value->player_id);
            }
            
            $player->setFirstName($value->first_name);
            $player->setLastName($value->last_name);
            $player->setSearchFullName($value->search_full_name);
            $player->setPosition($value->position);
            $player->setTeam($value->team);

            $playerInfo = Json::decode($player->getPlayerInfo(),1);
            $playerInfo['age'] = $value->age;
            $playerInfo['sport'] = $value->sport;
            $playerInfo['height'] = $value->height;
            $playerInfo['status'] = $value->status;
            $playerInfo['weight'] = $value->weight;
            $playerInfo['college'] = $value->college;
            $playerInfo['hashtag'] = $value->hashtag;
            $playerInfo['player_id'] = $value->player_id;
            $playerInfo['years_exp'] = $value->years_exp;
            $playerInfo['high_school'] = $value->birth_city;
            $playerInfo['birth_city'] = $value->birth_city;
            $playerInfo['birth_state'] = $value->birth_state;
            $playerInfo['birth_country'] = $value->birth_country;
            $playerInfo['birth_date'] = $value->birth_date;
            $playerInfo['first_name'] = $value->first_name;
            $playerInfo['last_name'] = $value->last_name;
            $playerInfo['search_rank'] = $value->search_rank;
            $playerInfo['news_updated'] = $value->news_updated;
            $playerInfo['injury_status'] = $value->injury_status;
            $playerInfo['search_full_name'] = $value->search_full_name;
            $playerInfo['search_first_name'] = $value->search_first_name;
            $playerInfo['search_last_name'] = $value->search_last_name;
            $player->setPlayerInfo($playerInfo);

            $apiInfo = Json::decode($player->getApiInfo(),1);
            $apiInfo['espn_id'] = $value->espn_id;
            $apiInfo['yahoo_id'] = $value->yahoo_id;
            $apiInfo['rotoworld_id'] = $value->rotoworld_id;
            $apiInfo['rotowire_id'] = $value->rotowire_id;
            $apiInfo['stats_id'] = $value->stats_id;
            $apiInfo['sportradar_id'] = $value->sportradar_id;
            $apiInfo['gsis_id'] = $value->gsis_id;
            $player->setApiInfo($apiInfo);

            $injuryInfo = Json::decode($player->getInjuryInfo(),1);
            $injuryInfo['injury_status'] = $value->injury_status;
            $injuryInfo['injury_notes'] = $value->injury_notes;
            $injuryInfo['injury_body_part'] = $value->injury_body_part;
            $injuryInfo['injury_start_date'] = $value->injury_start_date;
            $player->setInjuryInfo( $injuryInfo);

            $teamInfo = Json::decode($player->getTeamInfo(),1);
            $teamInfo['team'] = $value->team;
            $teamInfo['position'] = $value->position;
            $teamInfo['number'] = $value->number;
            $teamInfo['depth_chart_position'] = $value->depth_chart_position;
            $teamInfo['depth_chart_order'] = $value->depth_chart_order;
            $teamInfo['practice_participation'] = $value->practice_participation;
            $teamInfo['practice_description'] = $value->practice_description;
            $player->setTeamInfo($teamInfo);

            $this->playerCommand->save($player);
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function updateWrMetrics()
    {
//        $cbService = new Position\CbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//        $cbService->calculateMetrics();
//        $cbService->calculatePercentiles();
//
//        $safetyService = new Position\SafetyService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//        $safetyService->calculateMetrics();
//        $safetyService->calculatePercentiles();

//        $lbService = new Position\LBService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//        $lbService->calculateMetrics();
//        $lbService->calculatePercentiles();
//        $lbService->calculateSpecialScores();
//        $lbService->calculateSpecialPercentiles();
//
//        $dlService->calculateMetrics();
//        $dlService->calculatePercentiles();
//        $dlService->calculateSpecialScores();
//        $dlService->calculateSpecialPercentiles();
////        $dlService = new Position\DefLineService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//
//        $olService = new Position\OffLineService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//        $olService->calculateMetrics();
//        $olService->calculatePercentiles();
//        $olService->calculateSpecialScores();
//        $olService->calculateSpecialPercentiles();

//        $wrService = new Position\WrService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
////        $wrService->calculateMetrics();
////        $wrService->calculatePercentiles();
//        $wrService->calculateSpecialScores();
//        $wrService->calculateSpecialPercentiles();

        $rbService = new Position\RbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//        $rbService->calculateMetrics("RB");
//        $rbService->calculatePercentiles("RB");
        $rbService->calculateSpecialScores("RB");
        $rbService->calculateSpecialPercentiles("RB");
//
//        $teService = new Position\TeService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//        $teService->calculateMetrics("TE");
//        $teService->calculatePercentiles("TE");
//        $teService->calculateSpecialScores("TE");
//        $teService->calculateSpecialPercentiles("TE");
//
//        $qbService = new Position\QbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//        $qbService->calculateMetrics("QB");
//        $qbService->calculatePercentiles("QB");
//        $qbService->calculateSpecialScores("QB");
//        $qbService->calculateSpecialPercentiles("QB");
    }

    public function scrapCollegeJob()
    {
        $rbService = new Position\RbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
        $rbService->scrapCollegeJob();

//        $teService = new Position\TeService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//        $teService->scrapCollegeJob();

//        $wrService = new Position\WrService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//        $wrService->scrapCollegeJob();
    }

    public function getSalaries()
    {
        //Dk - https://www.draftkings.com/lineup/getavailableplayerscsv?contestTypeId=21&draftGroupId=28598
        //yahoo - https://dfyql-ro.sports.yahoo.com/v2/external/playersFeed/nfl
        //
    }
}