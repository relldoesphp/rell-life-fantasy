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
use Tightenco\Collect\Support\Collection;


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

            if ($sleeperId == 5826) {
                $found = true;
            }

            //fix height
            if ($value->height > 66 and $value->height < 82) {
                $value->heightInches = $value->height;
                $heightArray = [
                    '66' => '5\'6"',
                    '67' => '5\'7"',
                    '68' => '5\'8"',
                    '69' => '5\'9"',
                    '70' => '5\'10"',
                    '71' => '5\'11"',
                    '72' => '6\'0"',
                    '73' => '6\'1"',
                    '74' => '6\'2"',
                    '75' => '6\'3"',
                    '76' => '6\'4"',
                    '77' => '6\'5"',
                    '78' => '6\'6"',
                    '79' => '6\'7"',
                    '80' => '6\'8"',
                    '81' => '6\'9"',
                    '82' => '6\'10"',
                ];
                $value->height = $heightArray[$value->height];
            } else {
                if (!empty($value->height)) {
                    $playerInfo['height'] = $value->height;
                }
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
            if ($player->getPlayerInfo() != null) {
                $playerInfo = Json::decode($player->getPlayerInfo(),1);
            } else {
                $playerInfo = [];
            }

            $playerInfo['sport'] = $value->sport;

//sport            if ($player->getTeam() == 'Rookie') {
//                $playerInfo['age'] = $value->age;
////                if ($value->team == null) {
////                    $player->setTeam("FA");
////                }
////                if (!empty($value->height)) {
////                    $playerInfo['height'] = $value->height;
////                }
//
//                $playerInfo['status'] = $value->status;
//
////                if (!empty($value->weight)) {
////                    $playerInfo['weight'] = $value->weight;
////                }
//            } else {
//                $playerInfo['height'] = $value->height;
//                $playerInfo['heightInches'] = $value->heightInches;
//                $playerInfo['weight'] = $value->weight;
//            }
            if ($player->getTeam() != 'Rookie') {
                $player->setTeam($value->team);
                $playerInfo['height'] = $value->height;
                $playerInfo['heightInches'] = $value->heightInches;
                $playerInfo['weight'] = $value->weight;
                $playerInfo['birth_date'] = $value->birth_date;
            }
            $playerInfo['college'] = $value->college;
            $playerInfo['hashtag'] = $value->hashtag;
            $playerInfo['player_id'] = $value->player_id;
            $playerInfo['years_exp'] = $value->years_exp;
            $playerInfo['high_school'] = $value->birth_city;
            $playerInfo['birth_city'] = $value->birth_city;
            $playerInfo['birth_state'] = $value->birth_state;
            $playerInfo['birth_country'] = $value->birth_country;

            $playerInfo['first_name'] = $value->first_name;
            $playerInfo['last_name'] = $value->last_name;
            $playerInfo['search_rank'] = $value->search_rank;
            $playerInfo['news_updated'] = $value->news_updated;
            $playerInfo['injury_status'] = $value->injury_status;
            $playerInfo['search_full_name'] = $value->search_full_name;
            $playerInfo['search_first_name'] = $value->search_first_name;
            $playerInfo['search_last_name'] = $value->search_last_name;
            $player->setPlayerInfo($playerInfo);

            $apiInfo = ($player->getApiInfo() == null) ? [] : Json::decode($player->getApiInfo(),1);
            $apiInfo['espn_id'] = $value->espn_id;
            $apiInfo['yahoo_id'] = $value->yahoo_id;
            $apiInfo['rotoworld_id'] = $value->rotoworld_id;
            $apiInfo['rotowire_id'] = $value->rotowire_id;
            $apiInfo['stats_id'] = $value->stats_id;
            $apiInfo['sportradar_id'] = $value->sportradar_id;
            $apiInfo['gsis_id'] = $value->gsis_id;
            $player->setApiInfo($apiInfo);

            $injuryInfo = ($player->getInjuryInfo() == null) ? [] : Json::decode($player->getInjuryInfo(),1);
            $injuryInfo['injury_status'] = $value->injury_status;
            $injuryInfo['injury_notes'] = $value->injury_notes;
            $injuryInfo['injury_body_part'] = $value->injury_body_part;
            $injuryInfo['injury_start_date'] = $value->injury_start_date;
            $player->setInjuryInfo( $injuryInfo);

            $teamInfo = ($player->getTeamInfo() == null) ? [] : Json::decode($player->getTeamInfo(),1);
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
        $dlService = new Position\DefLineService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
        $dlService->calculateMetrics();
        $dlService->calculatePercentiles();
        $dlService->calculateSpecialScores();
        $dlService->calculateSpecialPercentiles();
    }

    public function updateLbMetrics()
    {
        $lbService = new Position\LBService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
        $lbService->calculateMetrics();
        $lbService->calculatePercentiles();
        $lbService->calculateSpecialScores();
        $lbService->calculateSpecialPercentiles();
    }

    public function updateCbMetrics()
    {
        $cbService = new Position\CbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
        $cbService->calculateMetrics();
        $cbService->calculatePercentiles();
    }

    public function updateSafetyMetrics()
    {
        $safetyService = new Position\SafetyService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
        $safetyService->calculateMetrics();
        $safetyService->calculatePercentiles();
    }


    public function scrapCollegeJob()
    {
 //       $rbService = new Position\RbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
 //       $rbService->findCfbId('2023');
//        $rbService->findCfbId('2020');
//        $rbService->findCfbId('2019');
//        $rbService->findCfbId('2018');
//        $rbService->findCfbId('2017');
//        $rbService->findCfbId('2016');
//        $rbService->scrapCollegeJob();
//        $rbService->makeCollegeBreakdown();
//        $this->updateRbMetrics();
//////
        $teService = new Position\TeService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);
       // $teService->findCfbId('2024');
        //$teService->scrapCollegeJob();
        $teService->makeCollegeBreakdown();

   //         $wrService = new Position\WrService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository, $this->sisApi);

  //          $wrService->findCfbId('2024');
 //           $wrService->scrapCollegeJob();
//          $wrService->makeCollegeBreakdown();
 //         $this->updateWrMetrics();

//        $wrService->findCfbId('2021');
//        $wrService->findCfbId('2020');
//        $wrService->findCfbId('2019');
//        $wrService->findCfbId('2018');
//        $wrService->findCfbId('2017');
//        $wrService->findCfbId('2016');



//        $qbService = new Position\QbService($this->db, $this->consoleAdapter, $this->playerCommand, $this->playerRepository);
//        $qbService->scrapCollegeJob();$collect = collect($collegePlayers);

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
        $positions = ['OL', 'DE', 'LB', 'CB', 'S'];

        $files = [
            'RB' => '/home/rell/Documents/rookie-rb.csv',
            'WR' => '/home/rell/Documents/rookie-wr.csv',
            'TE' => '/home/rell/Documents/rookie-te.csv',
            'OL' => '/home/rell/Documents/rookie-ol.csv',
            'DL' => '/home/rell/Documents/rookie-dl.csv',
            'DE' => '/home/rell/Documents/rookie-edge.csv',
            'LB' => '/home/rell/Documents/rookie-lb.csv',
            'CB' => '/home/rell/Documents/rookie-cb.csv',
            'S' => '/home/rell/Documents/rookie-s.csv',

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

        $indexes['OL'] = [
            'name' => 0,
            'position' => 1,
            'draft_year' => 2,
            'draft_pick' => 9,
            'weight' => 8,
            'heightInches' => 10,
            'popularity' => 9,
            'age' => 4,
            'arms' => 5,
            'height' => 7,
            'hand' => 3,
            'college' => 6
        ];

        $indexes['DL'] = [
            'name' => 0,
            'position' => 1,
            'draft_year' => 2,
            'draft_pick' => 10,
            'weight' => 6,
            'heightInches' => 9,
            'age' => 3,
            'arms' => 8,
            'height' => 5,
            'hand' => 4,
            'college' => 7
        ];

        $indexes['DE'] = [
            'name' => 0,
            'position' => 1,
            'draft_year' => 2,
            'draft_pick' => 10,
            'weight' => 8,
            'heightInches' => 9,
            'popularity' => 9,
            'age' => 3,
            'arms' => 5,
            'height' => 7,
            'hand' => 4,
            'college' => 6
        ];

        $indexes['LB'] = [
            'name' => 0,
            'position' => 1,
            'draft_year' => 2,
            'draft_pick' => 9,
            'weight' => 8,
            'heightInches' => 10,
            'age' => 3,
            'arms' => 5,
            'height' => 7,
            'hand' => 4,
            'college' => 6
        ];

        $indexes['CB'] = [
            'name' => 0,
            'position' => 1,
            'draft_year' => 2,
            'draft_pick' => 9,
            'weight' => 8,
            'heightInches' => 10,
            'age' => 3,
            'arms' => 5,
            'height' => 7,
            'hand' => 4,
            'college' => 6
        ];

        $indexes['S'] = [
            'name' => 0,
            'position' => 1,
            'draft_year' => 10,
            'draft_pick' => 8,
            'weight' => 7,
            'heightInches' => 9,
            'age' => 2,
            'arms' => 4,
            'height' => 6,
            'hand' => 3,
            'college' => 5
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
                if (count($name) < 2) {
                    $found = true;
                    $name = explode("\t", $data[$index['name']]);
                }
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
        $positions = ['OL', 'DE', 'LB', 'CB', 'S'];

        $files = [
            'RB' => '/home/rell/Documents/rookie-rb-metrics.csv',
            'WR' => '/home/rell/Documents/rookie-wr-metrics.csv',
            'TE' => '/home/rell/Documents/rookie-te-metrics.csv',
            'OL' => '/home/rell/Documents/rookie-ol-metrics.csv',
            'DL' => '/home/rell/Documents/rookie-dl-metrics.csv',
            'DE' => '/home/rell/Documents/rookie-edge-metrics.csv',
            'LB' => '/home/rell/Documents/rookie-lb-metrics.csv',
            'CB' => '/home/rell/Documents/rookie-cb-metrics.csv',
            'S' => '/home/rell/Documents/rookie-s-metrics.csv',
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

        $indexes['OL'] = [
            'name' => 0,
            'position' => 1,
            'shuttle' => 2,
            'cone' => 4,
            'fortyTime' => 7,
            'bench' => 5,
            'vertical' => 3,
            'broad' => 8,
        ];

        $indexes['DL'] = [
            'name' => 0,
            'position' => 1,
            'shuttle' => 2,
            'cone' => 3,
            'fortyTime' => 6,
            'bench' => 4,
            'vertical' => 5,
            'broad' => 7,
        ];

        $indexes['DE'] = [
            'name' => 0,
            'position' => 1,
            'shuttle' => 10,
            'cone' => 4,
            'fortyTime' => 4,
            'bench' => 8,
            'vertical' => 9,
            'broad' => 7,
        ];

        $indexes['LB'] = [
            'name' => 0,
            'position' => 1,
            'shuttle' => 2,
            'cone' => 3,
            'fortyTime' => 6,
            'bench' => 4,
            'vertical' => 5,
            'broad' => 7,
        ];

        $indexes['CB'] = [
            'name' => 0,
            'position' => 1,
            'shuttle' => 2,
            'cone' => 4,
            'fortyTime' => 7,
            'bench' => 6,
            'vertical' => 3,
            'broad' => 5,
        ];

        $indexes['S'] = [
            'name' => 0,
            'position' => 1,
            'shuttle' => 2,
            'cone' => 4,
            'fortyTime' => 7,
            'bench' => 5,
            'vertical' => 3,
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
                if (count($name) < 2) {
                    $found = true;
                    $name = explode("\t", $data[$index['name']]);
                }
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
                $metrics['shuttle'] = ($data[$index['shuttle']] == "-") ? null : $data[$index['shuttle']];
                $metrics['cone'] = ($data[$index['cone']] == "-") ? null : $data[$index['cone']];
                $metrics['benchPress'] = ($data[$index['bench']] == "-") ? null : $data[$index['bench']];
                $metrics['verticalJump'] = ($data[$index['vertical']] == "-") ? null : $data[$index['vertical']];
                $metrics['broadJump'] = ($data[$index['broad']] == "-") ? null : $data[$index['broad']];
                $metrics['fortyTime'] = ($data[$index['fortyTime']] == "-") ? null : $data[$index['fortyTime']];
//                if (!in_array($data[$index['fortyTime']], ["-", ""])) {
//                    $metrics['fortyTime'] = round((float)$data[$index['fortyTime']] + .05, 2);
//                } else {
//
//                }

                $player->setMetrics($metrics);

                $this->playerCommand->save($player);
                $pointer++;
                $progressBar->update($pointer);
            }
            $progressBar->finish();
        }
    }

    public function getCollegePassing($year)
    {
        $areas = [
            [
                "area" => "underneath",
                "low" => -10,
                "high" => 0,
            ],
            [
                "area" => "short",
                "low" => 1,
                "high" => 9,
            ],
            [
                "area" => "intermediate",
                "low" => 10,
                "high" => 20,
            ],
            [
                "area" => "deep",
                "low" => 21,
                "high" => 100,
            ],
            [
                "area" => "all",
                "low" => -20,
                "high" => 100,
            ]
        ];
        $players = [];
        $stats = [];
        $wrs = $this->playerRepository->findAllPlayers("WR");
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $wrs->count());
        $pointer = 0;
        $players = $this->sisApi->getCollegePassing($year, "receiving");
        foreach ($players as $player) {

        }
        foreach ($areas as $area) {
            $players = $this->sisApi->getCollegePassing($year, "receiving", [
                'ReceivingFilters.MinAirYards' => $area['low'],
                'ReceivingFilters.MaxAirYards' => $area['high'],

            ]);
            foreach ($players as $player) {
                $stats[$player['playerId']][$area['area']] = [
                    'playerId' => $player['playerId'],
                    'player' => $player['player'],
                    'yards' => $player['yards'],
                    'receptions' => $player['receptions'],
                    'ybContact' => $player['ybContact'],
                    'yaContact' => $player['yaContact'],
                    'aDoT' => $player['aDoT'],
                    'airYards' => $player['airYards'],
                    'tds' => $player['tDs'],
                    'yac' => $player['yac'],
                ];

                $statsByName[$player['player']][$area['area']] = [
                    'playerId' => $player['playerId'],
                    'player' => $player['player'],
                    'yards' => $player['yards'],
                    'tDs' => $player['tDs'],
                    'receptions' => $player['receptions'],
                    'ybContact' => $player['ybContact'],
                    'yaContact' => $player['yaContact'],
                    'aDoT' => $player['aDoT'],
                    'airYards' => $player['airYards'],
                    'tds' => $player['tDs'],
                    'yac' => $player['yac'],
                ];
            }
        }

        $wrs = $this->playerRepository->findAllPlayers("WR");
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $wrs->count());
        $pointer = 0;
        foreach ($wrs as $wr) {
            $receiving = [];
            $wr->decodeJson();
            $apiInfo = $wr->getApiInfo();
            $collegeStats = $wr->getCollegeStats();
            if (array_key_exists($year, $collegeStats)) {
                if (array_key_exists('cfb_id', $apiInfo) && array_key_exists($apiInfo['cfb_id'], $stats))
                    $receiving = $stats[$apiInfo['cfb_id']];
                else {
                    $firstName = $wr->getFirstName();
                    $lastName = $wr->getLastName();
                    if (array_key_exists($firstName." ".$lastName, $statsByName)) {
                        $receiving = $statsByName[$firstName . " " . $lastName];

                    } else {
                        continue;
                    }
                }


                if (empty($receiving)) {
                    continue;
                }
                $collegeStats[$year]['recBreakdown'] = $receiving;
                foreach ($areas as $area) {
                    if (!array_key_exists($area['area'], $receiving)) {
                        $receiving[$area['area']] = [
                            'playerId' => 0,
                            'player' => 0,
                            'yards' => 0,
                            'tDs' => 0,
                            'receptions' => 0,
                            'ybContact' => 0,
                            'yaContact' => 0,
                            'aDoT' => 0,
                            'airYards' => 0,
                            'tds' => 0,
                            'yac' => 0,
                        ];
                    }
                }

                if (!empty($collegeStats[$year]['recs']) && $receiving['all']['yards'] > 0) {
                    $totals = $receiving['all'];
                    $recPercents = [];
                    if (array_key_exists('deep', $receiving)) {
                        $recPercents['yards']['separation']['deepSeparation'] = round(($receiving['deep']['airYards'] / $totals['yards']) * 100,2);
                        $recPercents['yards']['ybc']['deepYBC'] = round(($receiving['deep']['ybContact'] / $totals['yards']) * 100,2);
                        $recPercents['yards']['yac']['deepYAC'] = round(($receiving['deep']['yaContact'] / $totals['yards']) * 100,2);
                        $recPercents['recs']['deepRecs'] = round(($receiving['deep']['receptions'] / $totals['receptions']) * 100,2);
                    } else {
                        $receiving['deep'] = [
                            'yards' => 0,
                            'tds' => 0,
                            'receptions' => 0,
                            'ybContact' => 0,
                            'yaContact' => 0,
                            'aDoT' => 0,
                            'airYards' => 0,
                            'yac' => 0
                        ];
                    }

                    if (array_key_exists('intermediate', $receiving)) {
                        $recPercents['yards']['separation']['midSeparation'] = round(($receiving['intermediate']['airYards'] / $totals['yards']) * 100, 2);
                        $recPercents['yards']['ybc']['midYBC'] = round(($receiving['intermediate']['ybContact'] / $totals['yards']) * 100, 2);
                        $recPercents['yards']['yac']['midYAC'] = round(($receiving['intermediate']['yaContact'] / $totals['yards']) * 100, 2);
                        $recPercents['recs']['midRecs'] = round(($receiving['intermediate']['receptions'] / $totals['receptions']) * 100,2);
                    } else {
                        $receiving['intermediate'] = [
                            'yards' => 0,
                            'tds' => 0,
                            'receptions' => 0,
                            'ybContact' => 0,
                            'yaContact' => 0,
                            'aDoT' => 0,
                            'airYards' => 0,
                            'yac' => 0
                        ];
                    }

                    if (array_key_exists('short', $receiving)) {
                        $recPercents['yards']['separation']['shortSeparation'] = round(($receiving['short']['airYards'] / $totals['yards']) * 100, 2);
                        $recPercents['yards']['yac']['shortYAC'] = round(($receiving['short']['yaContact'] / $totals['yards']) * 100,2);
                        $recPercents['yards']['ybc']['shortYBC'] = round(($receiving['short']['ybContact'] / $totals['yards']) * 100,2);
                        $recPercents['recs']['shortRecs'] = round(($receiving['short']['receptions'] / $totals['receptions']) * 100,2);
                    } else {
                        $receiving['short'] = [
                            'yards' => 0,
                            'tDs' => 0,
                            'receptions' => 0,
                            'ybContact' => 0,
                            'yaContact' => 0,
                            'aDoT' => 0,
                            'airYards' => 0,
                            'yac' => 0
                        ];
                    }

                    if (array_key_exists('underneath', $receiving)) {
                        $recPercents['yards']['ybc']['underYBC'] = round(($receiving['underneath']['ybContact'] / $totals['yards']) * 100, 2);
                        $recPercents['yards']['yac']['underYAC'] = round(($receiving['underneath']['yaContact'] / $totals['yards']) * 100,2);
                        $recPercents['recs']['underRecs'] = round(($receiving['underneath']['receptions'] / $totals['receptions']) * 100,2);
                    } else {
                        $receiving['underneath'] = [
                            'yards' => 0,
                            'tDs' => 0,
                            'receptions' => 0,
                            'ybContact' => 0,
                            'yaContact' => 0,
                            'aDoT' => 0,
                            'airYards' => 0,
                            'yac' => 0
                        ];
                    }
                    $collegeStats[$year]['recs'] = $totals['receptions'];
                    $collegeStats[$year]['recYds'] = $totals['yards'];
                    $collegeStats[$year]['recsTds'] = $totals['tds'];
                    $collegeStats[$year]['yac'] = $totals['yac'];
                    $collegeStats[$year]['recBreakdown'] = $receiving;
                    $collegeStats[$year]['recPercents'] = $recPercents;
                }
                $wr->setCollegeStats($collegeStats);
                $this->playerCommand->save($wr);

                $pointer++;
                $progressBar->update($pointer);
            }
        }
        $progressBar->finish();
    }
}