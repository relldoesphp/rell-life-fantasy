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

class TeamManager
{
    public $teamRepository;
    public $playerRepository;
    public $teamCommand;
    private $consoleAdapter;

    public function __construct(
        TeamRepositoryInterface $teamRepository,
        TeamCommandInterface $teamCommand,
        PlayerRepositoryInterface $playerRepository,
        Console $consoleAdapter
    ){
        $this->teamRepository = $teamRepository;
        $this->teamCommand = $teamCommand;
        $this->playerRepository = $playerRepository;
        $this->consoleAdapter = $consoleAdapter;
    }

    public function getTeam($name)
    {
        $team = $this->teamRepository->getTeamByName($name);
        $team->decodeJson();
        return $team;
    }

    public function buildDepthCharts()
    {
        $teams = $this->teamRepository->getTeams();
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($teams));
        $pointer = 0;
        foreach ($teams as $team) {
            $players = $this->playerRepository->getPlayersByTeam($team->getTeam());
            $depthChart = [];
            foreach($players as $player) {
                $player->decodeJson();
                $teamInfo = $player->getTeamInfo();
                $injuryInfo = $player->getInjuryInfo();

                if ($teamInfo['position'] == null) {
                    continue;
                }

//                if ($injuryInfo['status'] !== null) {
//                    $injured[] = $player;
//                }

                //mukbang
                if ($teamInfo['depth_chart_order'] == null) {
                    $depthChart[$teamInfo['position']]['bench'][] = $player->getAllInfo();
                } else {
                    $depthChart[$teamInfo['depth_chart_position']][$teamInfo['depth_chart_order']] = $player->getAllInfo();
                }
            }
            $team->setDepthChart($depthChart);

//            foreach ($depthChart as $position => $players) {
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
//                        if ($defScheme == "3-4") {
//                            switch ($position) {
//                                case "LOLB":
//                                case "WILL":
//                                case "WLB":
//                                    $dfront["LT"] = array_shift($depthChart[$position]);
//                                    break;
//                                case "LDE":
//                                case "DE":
//                                    $dfront["LG"] = array_shift($depthChart[$position]);
//                                    break;
//                                case "NT":
//                                    $dfront["C"] = array_shift($depthChart[$position]);
//                                    break;
//                                case "RDE":
//                                case "DT":
//                                    $dfront["RG"] = array_shift($depthChart[$position]);
//                                    break;
//                                case "ROLB":
//                                case "SLB":
//                                case "SAM":
//                                case "OLB":
//                                    $dfront["RT"] = array_shift($depthChart[$position]);
//                                    break;
//                                case "RILB":
//                                case "LILB":
//                                    $LBs["weak"] = array_shift($depthChart[$position]);
//                                case "LILB":
//                                case "MLB":
//                                case "MIKE":
//                                case "SILB":
//                                    $LBs["middle"] = array_shift($depthChart[$position]);
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
//                                    $LBs["strong"] = array_shift($depthChart[$position]);
//                                    break;
//                                case "LOLB":
//                                case "WILL":
//                                case "WLB":
//                                    $LBs["weak"] = array_shift($depthChart[$position]);
//                                    break;
//                                case "MLB":
//                                case "MIKE":
//                                    $LBs["middle"] = array_shift($depthChart[$position]);
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
//                $LBs['weak'] = array_shift($depthChart["MLB"]);
//            }
//            //get weak LB
//            if (array_key_exists("NB", $depthChart)) {
//                $jsVars['CB']['slot'] = array_shift($depthChart["NB"]);
//            } else {
//                if (array_key_exists(2, $depthChart["LCB"])) {
//                    $jsVars['CB']['slot'] = $depthChart["LCB"][2];
//                } elseif (array_key_exists(2, $depthChart["RCB"])) {
//                    $jsVars['CB']['slot'] = $depthChart["RCB"][2];
//                } else {
//                    $jsVars['CB']['slot'] = array_shift($depthChart["CB"]);
//                }
//            }
//
//
//            $team->CB = $jsVars['CB'];
//            $team->LBs = $LBs;
//            $team->dfront = $dfront;

            $this->teamCommand->saveTeam($team);
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }


}