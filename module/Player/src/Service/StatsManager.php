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
use Zend\Db\Adapter\AdapterInterface;
use Zend\Json\Json;
use Zend\ProgressBar\Adapter\Console;
use Zend\Http\Request;
use Zend\Http\Client;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Zend\ProgressBar\ProgressBar;
use Player\Model\Stats\SeasonStats;
use Player\Model\Stats\GameLog;

class StatsManager
{
    private $db;
    private $consoleAdapter;
    private $playerCommand;
    private $playerRepository;

    public function __construct(AdapterInterface $db,
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
        $this->statsRepository = $statsRepository;
        $this->statsCommand = $statsCommand;
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

    public function getSleeperGameLogs($year = 2018)
    {
        $week = 1;
        while ($week < 18) {
            $request = new Request();
            $uri = "https://api.sleeper.app/v1/stats/nfl/regular/{$year}/{$week}";
            $request->setUri($uri);

            $client = new Client();
            $response = $client->send($request);
            $html = $response->getBody();

            $json = (array) json_decode($html);
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

                if (array_key_exists("pass_rtg", $stats)) {
                    $stats['pass_rtg_avg'] = round($stats['pass_rtg']/$stats['gp'], 2);
                }

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
        $wrGameLogs = $this->statsRepository->getSeasonStatsByPosition("WR", "2018");
        $ranks = $this->statsRepository->makeSeasonRanks("2018", "WR");
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($wrGameLogs));
        $pointer = 0;
        foreach ($wrGameLogs as $wrGameLog) {
            $newRanks = [];
            $id = $wrGameLog->getId();
            $wrGameLog->decodeJson();
            $wrGameLogRanks = $wrGameLog->getRanks();
            foreach ($ranks as $name => $value) {
                $newRanks[$name] = (array_key_exists($id, $ranks[$name])) ? $ranks[$name][$id] : "";
            }

            $wrGameLog->setRanks($newRanks);
            $this->statsCommand->save($wrGameLog);
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Percentiles completed\n";
    }
}