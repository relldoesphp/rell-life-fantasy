<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/13/19
 * Time: 1:14 AM
 */

namespace Player\Service;

use Player\Model\Player\PlayerRepositoryInterface;
use Player\Model\Team\TeamCommandInterface;
use Player\Model\Team\TeamRepositoryInterface;
use Laminas\ProgressBar\Adapter\Console;
use Laminas\ProgressBar\ProgressBar;
use Player\Model\Team\Team;
use Player\Service\SportsInfoApi;
use Tightenco\Collect\Support\Collection;

class TeamManager
{
    public $teamRepository;
    public $playerRepository;
    public $teamCommand;
    private $teamCache;
    private $consoleAdapter;
    private $sportsInfoApi;

    public function __construct(
        TeamRepositoryInterface $teamRepository,
        TeamCommandInterface $teamCommand,
        PlayerRepositoryInterface $playerRepository,
        Console $consoleAdapter,
        $cache,
        SportsInfoApi $api
    ){
        $this->teamRepository = $teamRepository;
        $this->teamCommand = $teamCommand;
        $this->playerRepository = $playerRepository;
        $this->consoleAdapter = $consoleAdapter;
        $this->teamCache = $cache;
        $this->sportsInfoApi = $api;
    }

    public function getTeamById($id)
    {
        $team = $this->teamRepository->getTeamById($id);
        if ($team == null) {
            return null;
        }
        $team->decodeJson();
        return $team;
    }

    public function getTeam($name)
    {
        $team = $this->teamRepository->getTeamByName($name);
        if ($team == null) {
            return null;
        }
        $team->decodeJson();
        return $team;
    }

    public function saveTeam(Team $team)
    {
       return $this->teamCommand->saveTeam($team);
    }

    public function queryTeams($team)
    {
        return $this->teamRepository->queryTeams($team);
    }

    public function getDepthChart($team)
    {
        $depthChart = $this->teamCache->getItem($team);
        if ($depthChart == null) {
           $this->buildDepthCharts();
        }
        return json_decode($depthChart, 1);
    }

    public function buildDepthCharts()
    {
        $teams = $this->teamRepository->getTeams();
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($teams));
        $pointer = 0;
        foreach ($teams as $team) {
            $players = $this->playerRepository->getPlayersByTeam($team->getTeam());
            $collection = new Collection($players);

            $collection->map(function($player) {
                $player->decodeJson();
                if ($player->team_info['depth_chart_position'] == null) {
                    $player->team_info['depth_chart_position'] = $player->position;
                }
            });

            $depthChart = $collection->groupBy(function ($player, $key) {
                return $player->team_info['depth_chart_position'];
            })->sortBy('team_info.depth_chart_order');

            $this->teamCache->setItem($team->getTeam(),$depthChart->toJson());
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();

//            $offensiveLine = $collection->filter(function ($value, $key) {
//                return in_array($value->position, ["T", "G", "C", "OT", "LT", "RG", "LG", "RT"]);
//            })->groupBy(function ($player, $key) {
//                return $player->team_info['depth_chart_position'];
//            })->sortBy(function ($player, $key) {
//                return $player->pluck('team_info.depth_chart_order');
//            });

//            $depthChart = [];
//            $fullDepthChart = [];
//            foreach($players as $player) {
//                $player->decodeJson();
//                $teamInfo = $player->getTeamInfo();
//                $injuryInfo = $player->getInjuryInfo();
//
//                if ($teamInfo['position'] == null) {
//                    continue;
//                }
//
////                if ($injuryInfo['status'] !== null) {
////                    $injured[] = $player;
////                }
//
//                //mukbang
//                if ($teamInfo['depth_chart_order'] == null) {
//                    $depthChart[$teamInfo['position']]['bench'][] = $player->getId();
//                    $fullDepthChart[$teamInfo['position']]['bench'][] = $player->getAllInfo();
//                } else {
//                    $depthChart[$teamInfo['depth_chart_position']][$teamInfo['depth_chart_order']] = $player->getId();
//                    $fullDepthChart[$teamInfo['depth_chart_position']][$teamInfo['depth_chart_order']] = $player->getAllInfo();
//                }
//            }

//            $team->setDepthChart($depthChart);
//
//            if (array_key_exists('NT', $depthChart)) {
//                $defScheme = "3-4";
//            } else {
//                $defScheme = "4-3";
//            }
//
//            $LBs = [];
//            $dfront =[];
//
//            foreach ($fullDepthChart as $position => $players) {
//                foreach ($players as $depth => $player) {
//                    if (array_key_exists("injury_info", $player)
//                        && !in_array($player["injury_info"]["injury_status"], ["OUT", "IR", "Out"])) {
//                        if (in_array($position, ["LT", "LG", "C", "RG", "RT"])) {
//                            $oline[$position] = $player;
//                        }
//
//                        if (in_array($position, ["LWR", "SWR", "RWR", "TE"])) {
//                            $receivers[$position] = $player;
//                        }
//
//
//
//                        if ($defScheme == "3-4") {
//                            switch ($position) {
//                                case "LOLB":
//                                case "WILL":
//                                case "WLB":
//                                    $dfront["LT"] = array_shift($fullDepthChart[$position]);
//                                    break;
//                                case "LDE":
//                                case "DE":
//                                    $dfront["LG"] = array_shift($fullDepthChart[$position]);
//                                    break;
//                                case "NT":
//                                    $dfront["C"] = array_shift($fullDepthChart[$position]);
//                                    break;
//                                case "RDE":
//                                case "DT":
//                                    $dfront["RG"] = array_shift($fullDepthChart[$position]);
//                                    break;
//                                case "ROLB":
//                                case "SLB":
//                                case "SAM":
//                                case "OLB":
//                                    $dfront["RT"] = array_shift($fullDepthChart[$position]);
//                                    break;
//                                case "RILB":
//                                case "LILB":
//                                    $LBs["weak"] = array_shift($fullDepthChart[$position]);
//                                case "LILB":
//                                case "MLB":
//                                case "MIKE":
//                                case "SILB":
//                                    $LBs["middle"] = array_shift($fullDepthChart[$position]);
//                                    break;
//                            }
//                        }
//
//                        if ($defScheme == "4-3") {
//                            switch ($position) {
//                                case "LDE":
//                                    $dfront["LT"] = $player;
//                                    break;
//                                case "LDT":
//                                case "NT":
//                                    $dfront["LG"] = $player;
//                                    break;
//                                case "RDT":
//                                    $dfront["C"] = $player;
//                                    break;
//                                case "RDE":
//                                case "LEO":
//                                    $dfront["RG"] = $player;
//                                    break;
//                                case "ROLB":
//                                case "SLB":
//                                case "SAM":
//                                    $dfront["RT"] = $player;
//                                    $LBs["strong"] = array_shift($fullDepthChart[$position]);
//                                    break;
//                                case "LOLB":
//                                case "WILL":
//                                case "WLB":
//                                    $LBs["weak"] = array_shift($fullDepthChart[$position]);
//                                    break;
//                                case "MLB":
//                                case "MIKE":
//                                    $LBs["middle"] = array_shift($fullDepthChart[$position]);
//                                    break;
//                            }
//                        }
//
//                        if (in_array($position, ["LCB", "RCB"]) && in_array($depth,[1])) {
//                            $jsVars['CB'][$position][$depth] = $player;
//                        }
//
//                        break;
//                    } else {
//                        $injured[] = $player;
//                    }
//                }
//            }
//
//            //get weak LB
//            if (!array_key_exists("weak", $LBs) && $defScheme = "3-4") {
//                $LBs['weak'] = array_shift($fullDepthChart["MLB"]);
//            }
//            //get weak LB
//            if (array_key_exists("NB", $fullDepthChart)) {
//                $jsVars['CB']['slot'] = array_shift($fullDepthChart["NB"]);
//            } else {
//                if (array_key_exists('LCB', $fullDepthChart) && array_key_exists(2, $fullDepthChart["LCB"])) {
//                    $jsVars['CB']['slot'] = $fullDepthChart["LCB"][2];
//                } elseif (array_key_exists('RCB', $fullDepthChart) && array_key_exists(2, $fullDepthChart["RCB"])) {
//                    $jsVars['CB']['slot'] = $fullDepthChart["RCB"][2];
//                } else {
//                    $jsVars['CB']['slot'] = array_shift($fullDepthChart["CB"]);
//                }
//            }
//
//
//            $team->CB = $jsVars['CB'];
//            $team->LBs = $LBs;
//            $team->dfront = $dfront;
//
//            $this->teamCommand->saveTeam($team);
//            $this->teamCache->setItem($team->getTeam(),json_encode($fullDepthChart));
    }

    public function sync()
    {
        $teams = $this->sportsInfoApi->getTeams();
        foreach ($teams as $info) {
            $team = new Team();
            $team->setTeam($info['abbr']);
            $team->setCity($info['cityName']);
            $team->setName($info['nickName']);
            $team->setSisInfo($info);
            $this->teamCommand->saveTeam($team);
        }
    }

    public function createPassingStats(){
        $teams = $this->teamRepository->getTeams();
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($teams));
        $pointer = 0;
        foreach ($teams as $team) {
            $team->decodeJson();
            $sisInfo = $team->getSisInfo();
            // get underneath stats
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
                ]
            ];
            $passing = [];
            $totalTargets= 0;
            $totalReceptions = 0;
            $stats = $team->getStats();
            foreach ($areas as $area) {
                $players = [];
                $teamStats = [];
                $players = $this->sportsInfoApi->getPlayersQuery("2021", "receiving", [
                    'ReceivingFilters.MinAirYards' => $area['low'],
                    'ReceivingFilters.MaxAirYards' => $area['high'],
                    'GameFilters.Team' => $sisInfo['teamId']]);
                $teamStats['targets'] = 0;
                $teamStats['receptions'] = 0;
                $teamStats['tds'] = 0;
                $teamStats['yards'] = 0;
                $i = 0;
                $catchable = [];
                foreach ($players as $player) {
                    $teamStats['targets'] = $player['targets'] + $teamStats['targets'];
                    $teamStats['receptions'] = $player['receptions'] + $teamStats['receptions'];
                    $teamStats['tds'] = $player['tDs'] + $teamStats['tds'];
                    $teamStats['yards'] = $player['yards'] + $teamStats['yards'];
                    $catchable[] = $player['onTgtCatchRate'];
                }

                $passing[$area['area']]['teamTargets'] = $teamStats['targets'];
                $passing[$area['area']]['teamRecs'] = $teamStats['receptions'];
                $passing[$area['area']]['compRate'] = number_format($teamStats['receptions']/$teamStats['targets'],2);
                $passing[$area['area']]['tdRate'] = number_format($teamStats['tds']/$teamStats['receptions'],2);
                $passing[$area['area']]['ypc'] = number_format($teamStats['yards']/$teamStats['receptions'],2);
                $passing[$area['area']]['catchable'] = number_format(array_sum($catchable)/count($catchable),2);
                $totalTargets = $totalTargets + $teamStats['targets'];
                $totalReceptions = $totalReceptions + $teamStats['receptions'];
                foreach ($players as $player) {
                    $targetShare = number_format(($player['targets']/$teamStats['receptions']),2);
                    $recShare = number_format(($player['receptions']/$teamStats['receptions']), 2);
                    $passing[$area['area']]['players'][] = [
                        "playerId" => $player['playerId'],
                        "player" => $player['player'],
                        "receptions" => $player['receptions'],
                        "routes" => $player['routesRun'],
                        "targets" => $player['targets'],
                        "targetShare" => $targetShare,
                        "recShare" => $recShare
                    ];
                }
            }
            $passing['underneath']['targetShare'] = number_format($passing['underneath']['teamTargets']/$totalTargets,2);
            $passing['short']['targetShare'] = number_format($passing['short']['teamTargets']/$totalTargets,2);
            $passing['intermediate']['targetShare'] = number_format($passing['intermediate']['teamTargets']/$totalTargets, 2);
            $passing['deep']['targetShare'] = number_format($passing['deep']['teamTargets']/$totalTargets, 2);
            $stats['passingByArea'] = $passing;
            $team->setStats($passing);
            $this->teamCommand->updateTeam($team);
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }
}