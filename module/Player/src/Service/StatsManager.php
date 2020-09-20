<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/11/19
 * Time: 2:13 AM
 */

namespace Player\Service;

use Player\Model\Player\Player;
use Player\Model\Stats\StatsCommandInterface;
use Player\Model\Stats\StatsRepositoryInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Json\Json;
use Laminas\ProgressBar\Adapter\Console;
use Laminas\Http\Request;
use Laminas\Http\Client;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Laminas\ProgressBar\ProgressBar;
use Player\Model\Stats\SeasonStats;
use Player\Model\Stats\GameLog;
use Player\Service\SportsInfoApi;

class StatsManager
{
    private $db;
    private $consoleAdapter;
    private $playerCommand;
    private $playerRepository;
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
        $this->statsRepository = $statsRepository;
        $this->statsCommand = $statsCommand;
        $this->sisApi = $sisApi;
    }

    public function getSeasonStatsFromGames($year, $position) {
        $players = $this->playerRepository->findAllPlayers($position);
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($players));
        $pointer = 0;
        foreach ($players as $player) {
            if ($player->getSleeperId() == null) {
                continue;
            }
            $where =<<<EOT
sleeper_id = {$player->getSleeperId()} and year = {$year}
EOT;
            $gameLogs = $this->statsRepository->getGameLogsByWhere($where);
            if (!empty($gameLogs)) {
                $seasonTotals = [];
                foreach ($gameLogs as $gameLog) {
                    $stats = json_decode($gameLog['stats'], 1);
                    foreach ($stats as $key => $stat) {
                        if (!array_key_exists($key, $seasonTotals)) {
                            $seasonTotals[$key] = round($stat,2);
                        } else {
                            $seasonTotals[$key] = round(($stat + $seasonTotals[$key]),2);
                        }
                    }
                }

                $averages = ["rec_ypr", "rec_ypt", "rush_ypc", "pass_rtg"];
                foreach ($averages as $avKey) {
                    if (array_key_exists($avKey, $seasonTotals)) {
                        $seasonTotals[$avKey] = round(($seasonTotals[$avKey]/$seasonTotals['gp']),2);
                    }
                }

                if ($position == "RB" || $position == "WR" || $position == "RB" || $position == "TE") {
                    $seasonTotals['rush_td'] = (array_key_exists('rush_td', $seasonTotals)) ? $seasonTotals['rush_td'] : 0;
                    $seasonTotals['rec_td'] = (array_key_exists('rec_td', $seasonTotals)) ? $seasonTotals['rec_td'] : 0;
                    $seasonTotals['pass_td'] = (array_key_exists('pass_td', $seasonTotals)) ? $seasonTotals['pass_td'] : 0;
                    $seasonTotals['rush_yd'] = (array_key_exists('rush_yd', $seasonTotals)) ? $seasonTotals['rush_yd'] : 0;
                    $seasonTotals['rec_yd'] = (array_key_exists('rec_yd', $seasonTotals)) ? $seasonTotals['rec_yd'] : 0;
                    $seasonTotals["all_td"] = $seasonTotals['rush_td'] + $seasonTotals['rec_td'] + $seasonTotals['pass_td'];
                    $seasonTotals["all_yd"] = $seasonTotals['rush_yd'] + $seasonTotals['rec_yd'];
                }

                $seasonStat = $this->statsRepository->getSeasonStatsByWhere([
                    "sleeper_id = ?" => $player->getSleeperId(),
                    "year = ?" => $year
                ])->current();

                if ($seasonStat == false) {
                    continue;
                }

                $seasonStat->setStats($seasonTotals);
                $this->statsCommand->saveSeasonStat($seasonStat);

            }
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function getSeasonStats($year)
    {
        $list = $this->sisApi->getPlayers();
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($list));
        $pointer = 0;
        $count = 0;
        foreach ($list as $key => $info) {
            if (in_array($info['positionName'], ["QB", "RB", "WR", "TE"])) {
                $count++;
                $player = $this->playerRepository->findPlayerBySisId($info['playerId']);
                if ($count % 25 == 0) {
                    usleep(10000);
                }
                $sisStats = $this->sisApi->getSeasonStats($info['playerId'], $year, $info['positionName']);
                if ($player == null) {
                    $problem = true;
                    continue;
                }
                $seasonStat = $this->statsRepository->getSeasonStatsByWhere([
                    "sleeper_id = ?" => $player->getSleeperId(),
                    "year = ?" => $year
                ])->current();
                if ($seasonStat == false) {
                    $seasonStat = new SeasonStats();
                }
                $seasonStat->setSleeperId($player->getSleeperId());
                $seasonStat->setPlayerId($player->getId());
                $seasonStat->setYear($year);
                $seasonStat->decodeJson();
                $stats = $seasonStat->getStats();
                if (array_key_exists('passing', $sisStats) && !empty($sisStats['passing']) && array_key_exists('g', $sisStats['passing'])) {
                    $passingStats = [
                        "gp" => $sisStats['passing']['g'],
                        "gs" => $sisStats['passing']['starts'],
                        "pass_att" => $sisStats['passing']['attempts'],
                        "pass_cmp" => $sisStats['passing']['comp'],
                        "pass_int" => $sisStats['passing']['int'],
                        "pass_rtg_avg" => $sisStats['passing']['qbRating'],
                        "pass_ypa" => $sisStats['passing']['yardsPerAtt'],
                        "pass_ypc" => $sisStats['passing']['yardsPerComp'],
                        "pass_td" => $sisStats['passing']['td'],
                        "pass_yd" => $sisStats['passing']['yards'],
                        "passing" => $sisStats['passing'],
                    ];
                    $stats = array_merge($stats, $passingStats);
                }
                if (array_key_exists('rushing', $sisStats) && !empty($sisStats['rushing']) && array_key_exists('g', $sisStats['rushing'])) {
                    $rushingStats = [
                        "gp" => $sisStats['rushing']['g'],
                        "gs" => $sisStats['rushing']['starts'],
                        "rush_att" => $sisStats['rushing']['att'],
                        "rush_yd" => $sisStats['rushing']['yards'],
                        "rush_ypa" => $sisStats['rushing']['yardsPerAtt'],
                        "rush_td" => $sisStats['rushing']['td'],
                        "rush_fd" => $sisStats['rushing']['firstDown'],
                        "rush_long" => $sisStats['rushing']['long'],
                        "rushing" => $sisStats['rushing']
                    ];
                    $stats = array_merge($stats, $rushingStats);
                }
                if (array_key_exists('receiving', $sisStats) && !empty($sisStats['receiving']) && array_key_exists('g', $sisStats['receiving'])) {
                    $recStats = [
                        "gp" => $sisStats['receiving']['g'],
                        "gs" => $sisStats['receiving']['starts'],
                        "rec" => $sisStats['receiving']['recs'],
                        "rec_fd" => $sisStats['receiving']['firstDowns'],
                        "rec_td" => $sisStats['receiving']['td'],
                        "rec_yd" => $sisStats['receiving']['yards'],
                        "rec_tgt" => $sisStats['receiving']['targets'],
                        "rec_ypr" => $sisStats['receiving']['yardsPerRec'],
                        "rec_long" => $sisStats['receiving']['long'],
                        "receiving" => $sisStats['receiving']
                    ];
                    $stats = array_merge($stats, $recStats);
                }
                $stats = $this->calculateFantasyPoints($stats);
                $seasonStat->setStats($stats);
                if (empty($stats)) {
                    continue;
                }

                try {
                    $this->statsCommand->saveSeasonStat($seasonStat);
                } catch(\Exception $e) {
                    $wrong = true;
                }
                $pointer++;
                $progressBar->update($pointer);
            }
        }
        $progressBar->finish();
        return;


        $offensive = ["passing", "rushing", "receiving"];
        foreach ($offensive as $type) {
            $players = $this->sisApi->getPlayersQuery($year, $type);
            $progressBar = new ProgressBar($this->consoleAdapter, 0, count($players));
            $pointer = 0;
            foreach ($players as $playerInfo) {
                if (empty($playerInfo)) {
                    continue;
                }
                $player = $this->playerRepository->findPlayerBySisId($playerInfo['playerId']);
                if ($player == null) {
                    $problem = true;
                    continue;
                }
                $seasonStat = $this->statsRepository->getSeasonStatsByWhere([
                    "sleeper_id = ?" => $player->getSleeperId(),
                    "year = ?" => $year
                ])->current();
                if ($seasonStat == false) {
                    $seasonStat = new SeasonStats();
                }
                $seasonStat->setSleeperId($player->getSleeperId());
                $seasonStat->setPlayerId($player->getId());
                $seasonStat->setYear($year);
                $seasonStat->decodeJson();
                $stats = $seasonStat->getStats();
                switch ($type) {
                    case "passing":
                        $stats['passing'] = $playerInfo;
                        $formattedStats = [
                            "gp" => $stats['passing']['games'],
                            "gs" => $stats['passing']['games'],
                            "pass_att" => $stats['passing']['attempts'],
                            "pass_cmp" => $stats['passing']['completions'],
                            "pass_int" => $stats['passing']['ints'],
                            "pass_rtg_avg" => $stats['passing']['qbRating'],
                            "pass_ypa" => $stats['passing']['ypa'],
                            "pass_ypc" => $stats['passing']['yards'] / $stats['passing']['completions'],
                            "pass_td" => $stats['passing']['tDs'],
                            "pass_yd" => $stats['passing']['yards'],
                        ];
                        $stats = array_merge($stats, $formattedStats);
                        break;
                    case "rushing":
                        $stats['rushing'] = $playerInfo;
                        $formattedStats = [
                            "gp" => $stats['rushing']['games'],
                            "gs" => $stats['rushing']['games'],
                            "rush_att" => $stats['rushing']['carries'],
                            "rush_yd" => $stats['rushing']['yards'],
                            "rush_ypa" => $stats['rushing']['ypa'],
                            "rush_td" => $stats['rushing']['tDs'],
                            "rush_fd" => $stats['rushing']['firstDowns'],
                        ];
                        $stats = array_merge($stats, $formattedStats);
                        break;
                    case "receiving":
                        $stats['receiving'] = $playerInfo;
                        $formattedStats = [
                            "gp" => $stats['receiving']['games'],
                            "gs" => $stats['receiving']['games'],
                            "rec" => $stats['receiving']['receptions'],
                            "rec_fd" => $stats['receiving']['firstDowns'],
                            "rec_td" => $stats['receiving']['tDs'],
                            "rec_yd" => $stats['receiving']['yards'],
                            "rec_tgt" => $stats['receiving']['targets'],
                            "rec_ypr" => $stats['receiving']['ypc'],
                        ];
                        $stats = array_merge($stats, $formattedStats);
                    default:
                }
                $stats = $this->calculateFantasyPoints($stats);
                $seasonStat->setStats($stats);
                if (empty($stats)) {
                    continue;
                }

                try {
                    $this->statsCommand->saveSeasonStat($seasonStat);
                } catch(\Exception $e) {
                    $wrong = true;
                }
                $pointer++;
                $progressBar->update($pointer);
            }
            $progressBar->finish();
        }


    }

    public function getSleeperStats($year)
    {
        $request = new Request();
        $uri = "https://api.sleeper.app/v1/stats/nfl/regular/{$year}";
        $request->setUri($uri);

        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();

        $json = (array) json_decode($html);

        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($json));
        $pointer = 0;
        foreach ($json as $sleeperId => $stats) {
            if ($sleeperId == 3198) {
                print_r($stats);
                die();
            }
            // check for existing game log
            $seasonStat = $this->statsRepository->getSeasonStatsByWhere([
                "sleeper_id = ?" => $sleeperId,
                "year = ?" => $year
            ])->current();
            if ($seasonStat == false) {
                $seasonStat = new SeasonStats();
            }

            $seasonStat->setSleeperId($sleeperId);
            $seasonStat->setYear($year);
            $seasonStat->setStats($stats);
            // get player id
            $player = $this->playerRepository->findPlayerBySleeperId($sleeperId);
            if ($player !== false) {
                $seasonStat->setPlayerId($player->getId());
            }

            $this->statsCommand->saveSeasonStat($seasonStat);

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function getSleeperGameLogs($year)
    {
        $week = 16;
        while ($week < 19) {
            $request = new Request();
            $uri = "https://api.sleeper.app/v1/stats/nfl/regular/{$year}/{$week}";
            $request->setUri($uri);

            $client = new Client();
            $response = $client->send($request);
            $html = $response->getBody();

            $json = (array) json_decode($html);
            if (empty($json)) {
                break;
            }
            print("week {$week} \n");
            $progressBar = new ProgressBar($this->consoleAdapter, 0, count($json));
            $pointer = 0;
            foreach ($json as $key => $value) {
                // check for existing game log
                    $gameLog = $this->statsRepository->getGameLogsByWeekYearSleeper($week, $year, $key);
                    if ($gameLog == false) {
                    $gameLog = new GameLog();
                }

                $gameLog->setSleeperId($key);
                $gameLog->setWeek($week);
                $gameLog->setYear($year);
                $gameLog->setStats($value);
                // get player id
                $player = $this->playerRepository->findPlayerBySleeperId($key);
                if ($player !== false) {
                    $gameLog->setPlayerId($player->getId());
                }

                $this->statsCommand->saveGameLog($gameLog);

                $pointer++;
                $progressBar->update($pointer);
            }
            $progressBar->finish();
            $week++;
        }
    }

    public function getGameLogs($year)
    {
        $offensive = ["passing", "rushing", "receiving"];
        foreach ($offensive as $type) {
            $games = $this->sisApi->getPlayersQuery('2020', $type, ["TimeFilters.ByGame" => 1]);
            $progressBar = new ProgressBar($this->consoleAdapter, 0, count($games));
            $pointer = 0;
            foreach ($games as $playerInfo) {
                if (empty($playerInfo)) {
                    continue;
                }
                $player = $this->playerRepository->findPlayerBySisId($playerInfo['playerId']);
                if ($player == null) {
                    $problem = true;
                    continue;
                }

                    $gameLog = $this->statsRepository->getGameLogsByWeekYearSleeper(
                        $playerInfo['week'],
                        $year,
                        $player->getSleeperId());
                    if ($gameLog == false) {
                        $gameLog = new GameLog();
                    }
                    $gameLog->setSleeperId($player->getSleeperId());
                    $gameLog->setPlayerId($player->getId());
                    $gameLog->setYear($year);
                    $gameLog->setWeek($playerInfo['week']);
                    $gameLog->setOpponent($playerInfo['opp']);
                    $gameLog->setTeam($playerInfo['team']);
                    $gameLog->decodeJson();
                    $stats = $gameLog->getStats();
                    switch ($type) {
                        case "passing":
                            $stats['passing'] = $playerInfo;
                            $formattedStats = [
                                "gp" => $stats['passing']['games'],
                                "gs" => $stats['passing']['games'],
                                "pass_att" => $stats['passing']['attempts'],
                                "pass_cmp" => $stats['passing']['completions'],
                                "pass_int" => $stats['passing']['ints'],
                                "pass_rtg" => $stats['passing']['qbRating'],
                                "pass_ypa" => $stats['passing']['ypa'],
                                "pass_ypc" => $stats['passing']['yards'] / $stats['passing']['completions'],
                                "pass_td" => $stats['passing']['tDs'],
                                "pass_yd" => $stats['passing']['yards'],
                            ];
                            $stats = array_merge($stats, $formattedStats);
                            break;
                        case "rushing":
                            $stats['rushing'] = $playerInfo;
                            $formattedStats = [
                                "gp" => $stats['rushing']['games'],
                                "gs" => $stats['rushing']['games'],
                                "rush_att" => $stats['rushing']['carries'],
                                "rush_yd" => $stats['rushing']['yards'],
                                "rush_ypa" => $stats['rushing']['ypa'],
                                "rush_td" => $stats['rushing']['tDs'],
                                "rush_fd" => $stats['rushing']['firstDowns'],
                            ];
                            $stats = array_merge($stats, $formattedStats);
                            break;
                        case "receiving":
                            $stats['receiving'] = $playerInfo;
                            $formattedStats = [
                                "gp" => $stats['receiving']['games'],
                                "gs" => $stats['receiving']['games'],
                                "rec" => $stats['receiving']['receptions'],
                                "rec_fd" => $stats['receiving']['firstDowns'],
                                "rec_td" => $stats['receiving']['tDs'],
                                "rec_yd" => $stats['receiving']['yards'],
                                "rec_tgt" => $stats['receiving']['targets'],
                                "rec_ypr" => $stats['receiving']['ypc'],
                                "rec_ypt" => 0,
                            ];
                            $stats = array_merge($stats, $formattedStats);
                        default:
                    }
                    $stats = $this->calculateFantasyPoints($stats);
                    $gameLog->setStats($stats);
                    if (empty($stats)) {
                        continue;
                    }

                    try {
                        $this->statsCommand->saveGameLog($gameLog);
                    } catch(\Exception $e) {
                        $wrong = true;
                    }
                    $pointer++;
                    $progressBar->update($pointer);
                }
                $progressBar->finish();
            }
    }

    public function makeSeasonRanks($position, $year)
    {
        $seasonStats = $this->statsRepository->getSeasonStatsByPosition($position, $year);
        $ranks = $this->statsRepository->makeSeasonRanks($year, $position);
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($seasonStats));
        $pointer = 0;
        foreach ($seasonStats as $seasonStat) {
            $newRanks = [];
            $id = $seasonStat->getId();
            $seasonStat->decodeJson();
            $seasonRanks = $seasonStat->getRanks();
            foreach ($ranks as $name => $value) {
                $seasonRanks[$name] = (array_key_exists($id, $ranks[$name])) ? $ranks[$name][$id] : "";
            }


            $seasonStat->setRanks($seasonRanks);
            $this->statsCommand->saveSeasonStat($seasonStat);
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Percentiles completed\n";
    }

    public function makeSeasonAverages($position, $year)
    {
        $seasonStats = $this->statsRepository->getSeasonStatsByPosition($position, $year);
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($seasonStats));
        $pointer = 0;
        foreach ($seasonStats as $seasonStat) {
            // get player id
            $player = $this->playerRepository->findPlayerBySleeperId($seasonStat->getSleeperId());
            if ($player !== false) {
                $seasonStat->setPlayerId($player->getId());
            }

            $seasonStat->decodeJson();
            $stats = $seasonStat->getStats();
            if (array_key_exists("gp", $stats) && $stats["gp"] > 0) {
                if (array_key_exists('pts_ppr', $stats)) {
                    $stats['pts_ppr_avg'] = round($stats['pts_ppr']/$stats['gp'],2);
                }

                if (array_key_exists('pts_std', $stats)) {
                    $stats['pts_std_avg'] = round($stats['pts_std']/$stats['gp'],2);
                }

                if (array_key_exists('pts_half_ppr', $stats)) {
                    $stats['pts_half_ppr_avg'] = round($stats['pts_half_ppr']/$stats['gp'],2);
                }

                if (array_key_exists("rec_tgt", $stats)) {
                    $stats['tgt_avg'] = round($stats['rec_tgt']/$stats['gp'],2);
                }

                if (array_key_exists("rec", $stats)) {
                    $stats['rec_avg'] = round($stats['rec']/$stats['gp'],2);
                }

                if (array_key_exists("rec_yd", $stats)) {
                    $stats['rec_yd_avg'] = round($stats['rec_yd']/$stats['gp'],2);
                }

                if (array_key_exists("rec_fd", $stats)) {
                    $stats['rec_fd_avg'] = round($stats['rec_fd']/$stats['gp'], 2);
                }

                if (array_key_exists("rush_att", $stats)) {
                    $stats['rush_att_avg'] = round($stats['rush_att']/$stats['gp'],2);
                }

                if (array_key_exists("rush_yd", $stats)) {
                    $stats['rush_yd_avg'] = round($stats['rush_yd']/$stats['gp'],2);
                }

                if (array_key_exists("rush_fd", $stats)) {
                    $stats['rush_fd_avg'] = round($stats['rush_fd']/$stats['gp'], 2);
                }

                if (array_key_exists("cmp_pct", $stats)) {
                    $stats['cmp_pct_avg'] = round($stats['cmp_pct']/$stats['gp'], 2);
                }

//                if (array_key_exists("pass_rtg", $stats)) {
//                    $stats['pass_rtg_avg'] = round($stats['pass_rtg']/$stats['gp'], 2);
//                }

                if (array_key_exists("pass_fd", $stats)) {
                    $stats['pass_fd_avg'] = round($stats['pass_fd']/$stats['gp'], 2);
                }

                if ($player !== false) {
                    if (array_key_exists("off_snp", $stats) && array_key_exists("tm_off_snp", $stats)) {
                        $stats['snp_pct'] = round($stats['off_snp']/$stats['tm_off_snp'], 2);

                        $position = $player->getPosition();

                        if (in_array($position, ["RB","WR","TE"]) && array_key_exists("rec_tgt", $stats)) {
                            if (array_key_exists("rush_att", $stats)) {
                                $stats['opp_per_snap'] = round((($stats['rush_att'] + $stats['rec_tgt'])/$stats['off_snp']), 2);
                            } else {
                                $stats['opp_per_snap'] = round(($stats['rec_tgt']/$stats['off_snp']), 2);
                            }
                        }
                    }
                }
            }

            $seasonStat->setStats($stats);

            $this->statsCommand->saveSeasonStat($seasonStat);
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Averages completed\n";
    }


    public function makeGameLogRanks($position, $year)
    {
        $week = 1;
        while ($week < 18) {
            $wrGameLogs = $this->statsRepository->getGameLogsByPosition($position, $year, $week);
            if ($wrGameLogs->count() == 0) {
                break;
            }
            $ranks = $this->statsRepository->makeWeeklyRanks($week, $year, $position);
            $progressBar = new ProgressBar($this->consoleAdapter, 0, count($wrGameLogs));
            $pointer = 0;
            foreach ($wrGameLogs as $wrGameLog) {
                $newRanks = [];
                $id = $wrGameLog->getId();
                if ($id==826) {
                    $hopWk1 = true;
                }
                $wrGameLog->decodeJson();
                $wrGameLogRanks = $wrGameLog->getRanks();
                foreach ($ranks as $name => $value) {
                    $newRanks[$name] = (array_key_exists($id, $ranks[$name])) ? $ranks[$name][$id] : "";
                }

                $wrGameLog->setRanks($newRanks);
                $this->statsCommand->saveGameLog($wrGameLog);
                $pointer++;
                $progressBar->update($pointer);
            }
            $week++;
            $progressBar->finish();
            print "Ranks completed\n";
        }
    }

    public function calculateFantasyPoints($stats)
    {
        $scoring = [
            "pass_cmp"  => 0,
            "pass_incmp" => 0,
            "pass_td"   => 4,
            "pass_yd"   => 20,
            "rush_att"  => 0,
            "rush_td"   => 6,
            "rush_yd"   => 10,
            "rec"      => 1,
            "rec_td"    => 6,
            "rec_yd"    => 10,
            "pass_int"  => -2,
            "fumble"   => -2,
        ];

        $yards = ["rush_yd", "pass_yd", "rec_yd"];
        $multipliers = ["pass_cmp", "pass_td", "rush_att", "rush_td", "pass_int","fumble"];
        $points = 0;

        foreach ($stats as $key => $value) {
            if (in_array($key, $yards)) {
                $points = $points +  ($stats[$key] / $scoring[$key]);
            }

            if (in_array($key, $multipliers)) {
                $points = $points +  ($stats[$key] * $scoring[$key]);
            }
        }

        if (array_key_exists("rec", $stats)) {
            $stats['pts_ppr'] = $points +  ($stats["rec"] * 1);
            $stats['pts_half_ppr'] = $points +  ($stats["rec"] * .5);
            $stats["pts_std"] = $points;
        } else {
            $stats['pts_ppr'] = $points;
            $stats['pts_half_ppr'] = $points;
            $stats["pts_std"] = $points;
        }

        return $stats;
    }
}