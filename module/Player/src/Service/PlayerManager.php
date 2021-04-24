<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/23/19
 * Time: 10:46 PM
 */

namespace Player\Service;

use Player\Model\Player\Player;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Json\Json;
use Laminas\ProgressBar\Adapter\Console;
use Laminas\Http\Request;
use Laminas\Http\Client;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Player\Model\Stats\StatsCommandInterface;
use Player\Model\Stats\StatsRepositoryInterface;
use Laminas\ProgressBar\ProgressBar;
use Player\Service\Position;


class PlayerManager
{
    private $db;
    private $consoleAdapter;
    private $playerCommand;
    private $playerRepository;
    private $statsCommand;
    private $statsRepository;
    private $sisApi;

    public function __construct(
        AdapterInterface $db,
        Console $consoleAdapter,
        PlayerCommandInterface $playerCommand,
        PlayerRepositoryInterface $playerRepository,
        StatsCommandInterface $statsCommand,
        StatsRepositoryInterface $statsRepository,
        SportsInfoApi $sisApi
    )
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
        $this->playerRepository = $playerRepository;
        $this->playerCommand = $playerCommand;
        $this->statsCommand = $statsCommand;
        $this->statsRepository = $statsRepository;
        $this->sisApi = $sisApi;
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
                $ranks = $gameLog->getRanks();
                if (count($gameLog->getRanks()) == 1) {
                    $gameLog->setRanks([
                        "pts_ppr" => ""
                    ]);
                }
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

            if ($sleeperId == 7528) {
                $found = true;
            }
            
            $player = $this->playerRepository->findPlayerBySleeperId($sleeperId);
            if (empty($player)) {
                //check for rookies
                $rookie = $this->playerRepository->findRookiesByName(
                    $value->first_name,
                    $value->last_name,
                    $value->position
                );
                if (!empty($rookie)) {
                    $player = $rookie;
                    $player->setSleeperId($value->player_id);
                } else {
                    $player = new Player();
                    $player->setSleeperId($value->player_id);
                }
            }



            if ($player->getSleeperId() == null) {
                $player->setSleeperId($value->player_id);
            }
            
            $player->setFirstName($value->first_name);
            $player->setLastName($value->last_name);
            $player->setSearchFullName($value->search_full_name);
            $player->setPosition($value->position);
            $playerInfo = Json::decode($player->getPlayerInfo(),1);

            $playerInfo['sport'] = $value->sport;

            if ($player->getTeam() !== 'Rookie') {
                $player->setTeam($value->team);
                $playerInfo['age'] = $value->age;
                if ($value->team == null) {
                    $player->setTeam("FA");
                }
                if (!empty($value->height)) {
                    $playerInfo['height'] = $value->height;
                }

                $playerInfo['status'] = $value->status;

                if (!empty($value->weight)) {
                    $playerInfo['weight'] = $value->weight;
                }
            }

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
            if ($value->number == null) {
                $teamInfo['number'] = 0;
            }
            $teamInfo['depth_chart_position'] = $value->depth_chart_position;
            $teamInfo['depth_chart_order'] = $value->depth_chart_order;
            $teamInfo['practice_participation'] = $value->practice_participation;
            $teamInfo['practice_description'] = $value->practice_description;
            $player->setTeamInfo($teamInfo);

            $headshot = $player->getHeadshot();
            if ($headshot == null && $value->player_id != null) {
                $headshot = "https://sleepercdn.com/content/nfl/players/{$value->player_id}.jpg";
                $player->setHeadshot($headshot);
            }

            $this->playerCommand->save($player);
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function updateWrMetrics()
    {
        $wrService = new Position\WrService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
        $wrService->calculateMetrics();
        $wrService->calculatePercentiles();
        $wrService->calculateSpecialScores();
        $wrService->calculateSpecialPercentiles();
    }

    public function updateRbMetrics()
    {
        $rbService = new Position\RbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
        $rbService->calculateMetrics();
        $rbService->calculatePercentiles();
        $rbService->calculateSpecialScores();
        $rbService->calculateSpecialPercentiles();
    }

    public function updateTeMetrics()
    {
        $teService = new Position\TeService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
        $teService->calculateMetrics();
        $teService->calculatePercentiles();
        $teService->calculateSpecialScores();
        $teService->calculateSpecialPercentiles();
    }

    public function updateQbMetrics()
    {
        $qbService = new Position\QbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
        $qbService->calculateMetrics();
        $qbService->calculatePercentiles();
        $qbService->calculateSpecialScores();
        $qbService->calculateSpecialPercentiles();
    }

    public function updateOlMetrics()
    {
        $olService = new Position\OffLineService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
        $olService->calculateMetrics();
        $olService->calculatePercentiles();
        $olService->calculateSpecialScores();
        $olService->calculateSpecialPercentiles();
    }

    public function updateDlMetrics()
    {
        $dlService = new Position\DefLineService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
        $dlService->calculateMetrics();
        $dlService->calculatePercentiles();
        $dlService->calculateSpecialScores();
        $dlService->calculateSpecialPercentiles();
    }

    public function updateLbMetrics()
    {
        $lbService = new Position\LBService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
        $lbService->calculateMetrics();
        $lbService->calculatePercentiles();
        $lbService->calculateSpecialScores();
        $lbService->calculateSpecialPercentiles();
    }

    public function updateCbMetrics()
    {
        $cbService = new Position\CbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
        $cbService->calculateMetrics();
        $cbService->calculatePercentiles();
    }

    public function updatteSafetiesMetrics()
    {
        $safetyService = new Position\SafetyService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
        $safetyService->calculateMetrics();
        $safetyService->calculatePercentiles();
    }


    public function scrapCollegeJob()
    {
//        $rbService = new Position\RbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
//        $rbService->scrapCollegeJob();
//
//        $teService = new Position\TeService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
//        $teService->scrapCollegeJob();

         $wrService = new Position\WrService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
         $wrService->scrapCollegeJob();

//        $qbService = new Position\QbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//        $qbService->scrapCollegeJob();

    }

    public function getSalaries()
    {
        //Dk - https://www.draftkings.com/lineup/getavailableplayerscsv?contestTypeId=21&draftGroupId=28598
        //yahoo - https://dfyql-ro.sports.yahoo.com/v2/external/playersFeed/nfl
        //
    }

    public function syncSisIds()
    {
//        $playerIds = $this->sisApi->getPlayerIds();
//        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($playerIds));
//        $pointer = 0;
//        foreach ($playerIds as $playerInfo) {
//           $player =  $this->playerRepository->findPlayerByGsisId($playerInfo['gsisPlayerId']);
//           if ($player != null) {
//               $player->setSisId($playerInfo['sisPlayerId']);
//               $this->playerCommand->updatePlayer($player);
//           }
//           $pointer++;
//           $progressBar->update($pointer);
//        }
//        $progressBar->finish();

        $players = $this->sisApi->getPlayers();
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($players));
        $pointer = 0;
        foreach ($players as $playerInfo) {
            $player =  $this->playerRepository->findPlayerBySisId($playerInfo['playerId']);
            if ($player == null) {
                $player = $this->playerRepository->findPlayerByInfo($playerInfo['firstName'], $playerInfo['lastName'], $playerInfo['positionName']);
                if ($player != null) {
                    $player->setSisId($playerInfo['playerId']);
                    $this->playerCommand->updatePlayer($player);
                }
            }
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function playerProfilerInfo()
    {
        $positions = ['RB','WR','TE'];

        $files = [
            'RB' => '/home/rell/Documents/rookie-rb.csv',
            'WR' => '/home/rell/Documents/rookie-wr.csv',
            'TE' => '/home/rell/Documents/rookie-te.csv',
        ];

        $indexes['RB'] = [
            'name' => 0,
            'position' => 1,
            'draft_year' => 2,
            'draft_pick' => 5,
            'weight' => 3,
            'heightInches' => 4,
            'popularity' => 6,
            'age' => 7,
            'arms' => 8,
            'height' => 9,
            'hand' => 10,
            'college' => 11
        ];

        $indexes['WR'] = [
            'name' => 0,
            'position' => 1,
            'draft_year' => 8,
            'draft_pick' => 7,
            'weight' => 6,
            'heightInches' => 10,
            'popularity' => 11,
            'age' => 2,
            'arms' => 3,
            'height' => 5,
            'hand' => 9,
            'college' => 4
        ];

        $indexes['TE'] = [
            'name' => 0,
            'position' => 1,
            'draft_year' => 3,
            'draft_pick' => 8,
            'weight' => 2,
            'heightInches' => 11,
            'popularity' => 9,
            'age' => 5,
            'arms' => 7,
            'height' => 6,
            'hand' => 4,
            'college' => 10
        ];

        $fractions = [
            "1/2" => .5,
            "3/4" => .75,
            "1/4" => .25,
            "1/8" => .125,
            "3/8" => .375,
            "5/8" => .625,
            "6/8" => .75,
            "7/8" => .875
        ];

        foreach ($positions as $position) {
            $index = [];
            $csv = array_map('str_getcsv', file($files[$position]));
            $index = $indexes[$position];
            $progressBar = new ProgressBar($this->consoleAdapter, 0, (count($csv) - 1));
            $pointer = 0;
            foreach ($csv as $data) {
                if ($data[0] == "Full Name") {
                    continue;
                }

                $name = explode(' ', $data[$index['name']]); // Replaces all spaces with hyphens.
                //check for player
                $player = $this->playerRepository->findPlayerByInfo(
                    $name[0],
                    $name[1],
                    $data[$index['position']]
                );

                if (empty($player)) {
                    $player = new Player();
                    $player->setFirstName($name[0]);
                    $player->setLastName($name[1]);

                    $searchName = str_replace(' ', '', $data[$index['name']]); // Replaces all spaces with hyphens.
                    $searchName = str_replace('.', '', $searchName);
                    $searchName = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $searchName));

                    $player->setSearchFullName($searchName);
                    $player->setPosition($data[$index['position']]);
                    $player->setTeam("Rookie");
                }

                $playerInfo = Json::decode($player->getPlayerInfo(),1);
                $playerInfo['draft_pick'] = $data[$index['draft_pick']];
                $playerInfo['draft_year'] = $data[$index['draft_year']];
                $playerInfo['arms'] = $data[$index['arms']];
                $playerInfo['height'] = $data[$index['height']];
                $playerInfo['heightInches'] = $data[$index['heightInches']];
                $playerInfo['weight'] = str_replace(" lbs", "", $data[$index['weight']]);
                $playerInfo['college'] = $data[$index['college']];
                $playerInfo['age'] = $data[$index['age']];

                $arms = explode(" ", $data[$index['arms']]);
                if (array_key_exists(1, $arms) && array_key_exists($arms[1], $fractions)) {
                    $playerInfo['armsInches'] = (int)$arms[0] + $fractions[$arms[1]];
                } else {
                    $playerInfo['armsInches'] = $arms[0];
                }

                $hands = explode(" ", $data[$index['hand']]);
                if (array_key_exists(1, $hands) && !empty($hands[1])) {
                    $playerInfo['hands'] = (int)$hands[0] + $fractions[$hands[1]];
                } else {
                    $playerInfo['hands'] = $hands[0];
                }

                $player->setPlayerInfo($playerInfo);

                $this->playerCommand->save($player);
                $pointer++;
                $progressBar->update($pointer);
            }
            $progressBar->finish();
        }
    }

    public function playerProfilerMetrics()
    {
        $positions = ['RB','WR','TE'];

        $files = [
            'RB' => '/home/rell/Documents/rookie-rb-metrics.csv',
            'WR' => '/home/rell/Documents/rookie-wr-metrics.csv',
            'TE' => '/home/rell/Documents/rookie-te-metrics.csv',
        ];

        $indexes['RB'] = [
            'name' => 0,
            'position' => 1,
            'shuttle' => 3,
            'cone' => 2,
            'fortyTime' => 4,
            'bench' => 5,
            'vertical' => 7,
            'broad' => 6,
        ];

        $indexes['WR'] = [
            'name' => 0,
            'position' => 1,
            'shuttle' => 2,
            'cone' => 3,
            'fortyTime' => 7,
            'bench' => 4,
            'vertical' => 5,
            'broad' => 6,
        ];

        $indexes['TE'] = [
            'name' => 0,
            'position' => 1,
            'shuttle' => 2,
            'cone' => 3,
            'fortyTime' => 5,
            'bench' => 4,
            'vertical' => 7,
            'broad' => 6,
        ];

        $fractions = [
            "1/2" => .5,
            "3/4" => .75,
            "1/4" => .25,
            "1/8" => .125,
            "3/8" => .375,
            "5/8" => .625,
            "6/8" => .75,
            "7/8" => .875
        ];

        foreach ($positions as $position) {
            $index = [];
            $csv = array_map('str_getcsv', file($files[$position]));
            $index = $indexes[$position];
            $progressBar = new ProgressBar($this->consoleAdapter, 0, (count($csv) - 1));
            $pointer = 0;
            foreach ($csv as $data) {
                if ($data[0] == "Full Name") {
                    continue;
                }

                $name = explode(' ', $data[$index['name']]); // Replaces all spaces with hyphens.
                //check for player
                $player = $this->playerRepository->findPlayerByInfo(
                    $name[0],
                    $name[1],
                    $data[$index['position']]
                );

                if (empty($player)) {
                    $player = new Player();
                    $player->setFirstName($name[0]);
                    $player->setLastName($name[1]);

                    $searchName = str_replace(' ', '', $data[$index['name']]); // Replaces all spaces with hyphens.
                    $searchName = str_replace('.', '', $searchName);
                    $searchName = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $searchName));

                    $player->setSearchFullName($searchName);
                    $player->setPosition($data[$index['position']]);
                    $player->setTeam("Rookie");
                }

                $metrics = Json::decode($player->getMetrics(),1);
                $metrics['shuttle'] = $data[$index['shuttle']];
                $metrics['cone'] = $data[$index['cone']];
                $metrics['benchPress'] = $data[$index['bench']];
                $metrics['verticalJump'] = $data[$index['vertical']];
                $metrics['broadJump'] = $data[$index['broad']];

                if (!in_array($data[$index['fortyTime']], ["-", ""])) {
                    $metrics['fortyTime'] = round((float)$data[$index['fortyTime']] + .05, 2);
                } else {
                    $metrics['fortyTime'] = $data[$index['fortyTime']];
                }

                $player->setMetrics($metrics);

                $this->playerCommand->save($player);
                $pointer++;
                $progressBar->update($pointer);
            }
            $progressBar->finish();
        }
    }
}