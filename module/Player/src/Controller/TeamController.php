<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/15/19
 * Time: 1:11 AM
 */

namespace Player\Controller;


use Player\Service\TeamManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class TeamController extends AbstractActionController
{
    public $teamManager;

    public function __construct(TeamManager $teamManager)
    {
        $this->teamManager = $teamManager;

    }

    public function matchupAction()
    {
        $team = $this->params()->fromQuery("off");
        $def = $this->params()->fromQuery("def");

        $team1 = $this->teamManager->getTeam($team);
        $team2 = $this->teamManager->getTeam($def);

        $teams = [
            "team1" => $team1,
            "team2" => $team2
        ];

        foreach($teams as $key => $team){
            //build offensive line
            $team->decodeJson();
            $depthChart = $team->depth_chart;
            $defScheme = $team->def_base;
            $LBs = [];

            // Tampa Corner Fix
            if (!array_key_exists("LCB", $depthChart)) {
                $depthChart["LCB"] = $depthChart["CB"];
                $team->depth_chart = $depthChart;
            }

            // Fix array orders
//            foreach ($depthChart as $position => $players) {
//                $depthChart[$position] = array_values($players);
//            }

            $injured = [];

            //build oline
            $offLine["LT"] = array_values($depthChart["LT"]);
            $offLine["LG"] = array_values($depthChart["LG"]);
            $offLine["C"] = array_values($depthChart["C"]);
            $offLine["RG"] = array_values($depthChart["RG"]);
            $offLine["RT"] = array_values($depthChart["RT"]);
            $startingOLine = [];
            foreach($offLine as $position => $players) {
                foreach ($players as $player) {
                    if (array_key_exists("injury_info", $player)
                        && !in_array($player["injury_info"]["injury_status"], ["OUT", "IR", "Out"])) {
                        $startingOLine[$position] = $player;
                        break;
                    } else {
                       continue;
                    }
                }
            }

            if (empty($startingOLine["LT"])) {
                $startingOLine["LT"] = array_shift($depthChart["OT"]["bench"]);
            }

            if (empty($startingOLine["RT"])) {
                $startingOLine["RT"] = array_shift($depthChart["OT"]["bench"]);
            }

            if (empty($startingOLine["LG"])) {
                $startingOLine["LG"] = array_shift($depthChart["G"]["bench"]);
            }

            if (empty($startingOLine["RG"])) {
                $startingOLine["RG"] = array_shift($depthChart["G"]["bench"]);
            }

            if (empty($startingOLine["C"])) {
                $startingOLine["C"] = array_shift($depthChart["GC"]["bench"]);
            }

            $oline = $startingOLine;

            $dfront = [];
            foreach ($depthChart as $position => $players) {
                if ($defScheme == "3-4") {
                    switch ($position) {
                        case "LOLB":
                        case "WILL":
                        case "WLB":
                            $dfront["LT"] = $players;
                            break;
                        case "LDE":
                        case "DE":
                            $dfront["LG"] = $players;
                            break;
                        case "NT":
                            $dfront["C"] = $players;
                            break;
                        case "RDE":
                        case "DT":
                            $dfront["RG"] = $players;
                            break;
                        case "ROLB":
                        case "SLB":
                        case "SAM":
                        case "OLB":
                            $dfront["RT"] = $players;
                            break;
                        case "RILB":
                        case "LILB":
                            $LBs["weak"] = $players;
                        case "LILB":
                        case "MLB":
                        case "MIKE":
                        case "SILB":
                            $LBs["middle"] = $players;
                            break;
                    }
                }

                if ($defScheme == "4-3") {
                    switch ($position) {
                        case "LDE":
                            $dfront["LT"] = $players;
                            break;
                        case "LDT":
                        case "NT":
                            $dfront["LG"] = $players;
                            break;
                        case "RDT":
                        case "DT":
                            $dfront["C"] = $players;
                            break;
                        case "RDE":
                        case "LEO":
                            $dfront["RG"] = $players;
                            break;
                        case "ROLB":
                        case "SLB":
                        case "SAM":
                            $dfront["RT"] = $players;
                            $LBs["strong"] = $players;
                            break;
                        case "LOLB":
                        case "WILL":
                        case "WLB":
                            $LBs["weak"] = $players;
                            break;
                        case "MLB":
                        case "MIKE":
                            $LBs["middle"] = $players;
                            break;
                    }
                }
            }

            switch ($team->getTeam()) {
                case "KC":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["NT"]);
                    $dfront["C"] = array_values($depthChart["DT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["RDE"]);
                    break;
                default:
                    $dfront["LT"] = array_values($dfront["LT"]);
                    $dfront["LG"] = array_values($dfront["LG"]);
                    $dfront["C"] = array_values($dfront["C"]);
                    $dfront["RG"] = array_values($dfront["RG"]);
                    $dfront["RT"] = array_values($dfront["RT"]);
            }
            //build oline

            $startingDline = [];

            foreach($dfront as $position => $players) {
                foreach ($players as $player) {
                    if (array_key_exists("injury_info", $player)
                        && !in_array($player["injury_info"]["injury_status"], ["OUT", "IR", "Out"])) {
                        $startingDLine[$position] = $player;
                        break;
                    } else {
                        continue;
                    }
                }
            }





            //build D front
//            if ($defScheme == "3-4") {
//                switch($team->getName()) {
//                    default:
//                        $defLine["LT"] = array_values($depthChart["LOLB"]);
//                        $defLine["LG"] = array_values($depthChart["LDE"]);
//                        $defLine["C"] = array_values($depthChart["NT"]);
//                        $defLine["RG"] = array_values($depthChart["RDE"]);
//                        $defLine["RT"] = array_values($depthChart["ROLB"]);
//                }
//            }
//
//            if ($defScheme == "4-3") {
//                switch($team->getName()) {
//                    default:
//                        $defLine["LT"] = array_values($depthChart["LDE"]);
//                        $defLine["LG"] = array_values($depthChart["LDT"]);
//                        $defLine["C"] = array_values($depthChart["RDT"]);
//                        $defLine["RG"] = array_values($depthChart["RDE"]);
//                        $defLine["RT"] = array_values($depthChart["ROLB"]);
//                }
//            }

            foreach ($depthChart as $position => $players) {
                foreach ($players as $depth => $player) {
                    if (array_key_exists("injury_info", $player)
                        && !in_array($player["injury_info"]["injury_status"], ["OUT", "IR", "Out"])) {

                        $anything = $player;
                        if (in_array($position, ["LT", "LG", "C", "RG", "RT"])) {
                            $oline[$position] = $player;
                        }

                        if (in_array($position, ["LWR", "SWR", "RWR", "TE"])) {
                            $receivers[$position] = $player;
                        }

                        if ($defScheme == "3-4") {
                            switch ($position) {
                                case "LOLB":
                                case "WILL":
                                case "WLB":
                                    $dfront["LT"] = array_shift($depthChart[$position]);
                                    break;
                                case "LDE":
                                case "DE":
                                    $dfront["LG"] = array_shift($depthChart[$position]);
                                    break;
                                case "NT":
                                    $dfront["C"] = array_shift($depthChart[$position]);
                                    break;
                                case "RDE":
                                case "DT":
                                    $dfront["RG"] = array_shift($depthChart[$position]);
                                    break;
                                case "ROLB":
                                case "SLB":
                                case "SAM":
                                case "OLB":
                                    $dfront["RT"] = array_shift($depthChart[$position]);
                                    break;
                                case "RILB":
                                case "LILB":
                                    $LBs["weak"] = array_shift($depthChart[$position]);
                                case "LILB":
                                case "MLB":
                                case "MIKE":
                                case "SILB":
                                    $LBs["middle"] = array_shift($depthChart[$position]);
                                    break;
                            }
                        }

                        if ($defScheme == "4-3") {
                            switch ($position) {
                                case "LDE":
                                    $dfront["LT"] = $player;
                                    break;
                                case "LDT":
                                case "NT":
                                    $dfront["LG"] = $player;
                                    break;
                                case "RDT":
                                    $dfront["C"] = $player;
                                    break;
                                case "RDE":
                                case "LEO":
                                    $dfront["RG"] = $player;
                                    break;
                                case "ROLB":
                                case "SLB":
                                case "SAM":
                                    $dfront["RT"] = $player;
                                    $LBs["strong"] = array_shift($depthChart[$position]);
                                    break;
                                case "LOLB":
                                case "WILL":
                                case "WLB":
                                    $LBs["weak"] = array_shift($depthChart[$position]);
                                    break;
                                case "MLB":
                                case "MIKE":
                                    $LBs["middle"] = array_shift($depthChart[$position]);
                                    break;
                            }
                        }

                        if (in_array($position, ["LCB", "RCB"]) && in_array($depth,[1])) {
                            $jsVars['CB'][$position][$depth] = $player;
                        }

                        break;
                    } else {
                        $injured[] = $player;
                    }
                }
            }

            //get weak LB
            if (!array_key_exists("weak", $LBs) && $defScheme = "3-4") {
                $LBs['weak'] = array_shift($depthChart["MLB"]);
            }
            //get weak LB
            if (array_key_exists("NB", $depthChart)) {
                $jsVars['CB']['slot'] = array_shift($depthChart["NB"]);
            } else {
                if (array_key_exists(2, $depthChart["LCB"])) {
                    $jsVars['CB']['slot'] = $depthChart["LCB"][2];
                } elseif (array_key_exists(2, $depthChart["RCB"])) {
                    $jsVars['CB']['slot'] = $depthChart["RCB"][2];
                } else {
                    $jsVars['CB']['slot'] = array_shift($depthChart["CB"]);
                }
            }


            $team->CB = $jsVars['CB'];
            $team->LBs = $LBs;
            $team->dfront = $startingDLine;
            $team->oline = $startingOLine;
            $jsVars[$key] = $team;
        }


        $viewModel = new ViewModel([
            'jsVars' => $jsVars,
            'team1' => $jsVars['team1'],
            'team2' => $jsVars['team2']
        ]);

        return $viewModel;
    }

    public function depthChartAction(){

    }
}