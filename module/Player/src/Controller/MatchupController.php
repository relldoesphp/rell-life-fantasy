<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/27/19
 * Time: 6:09 PM
 */

namespace Player\Controller;

use Player\Service\MatchupManager;
use Player\Service\TeamManager;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;

class MatchupController extends AbstractActionController
{
    public $teamManager;
    public $matchupManager;

    public function __construct(TeamManager $teamManager, MatchupManager $matchupManager)
    {
        $this->teamManager = $teamManager;
        $this->matchupManager = $matchupManager;

    }

    public function indexAction(){
        $week = $this->params()->fromQuery("week");
        $year = $this->params()->fromQuery("year");

        $matchups = $this->matchupManager->getMatchups($week,$year);

        $viewModel = new ViewModel([
            'matchups' => $matchups,
        ]);

        return $viewModel;

    }

    public function viewAction()
    {
        $id = $this->params("id");
        $matchup = $this->matchupManager->getMatchupById($id);

        $team1 = $this->teamManager->getTeam($matchup->away);
        $team2 = $this->teamManager->getTeam($matchup->home);

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
            if (array_key_exists("LT", $depthChart)) {
                $offLine["LT"] = array_values($depthChart["LT"]);
            } else {
                $offLine["LT"] = array_values($depthChart["OT"]["bench"]);
            }

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

            if (empty($startingOLine["RT"]) && count($offLine["RT"]) > 1) {
                $startingOLine["RT"] = array_shift($depthChart["OT"]["bench"]);
            } elseif (empty($startingOLine["RT"]) && count($offLine["RT"]) == 1) {
                $startingOLine["RT"] = $depthChart["LT"]["2"];
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
            $startingDline = [];

            switch ($team->getTeam()) {
                case "NO":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["LDT"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["SLB"]);
                    $LBs["weak"] = array_values($depthChart["WLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    break;
                case "ATL":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["LDT"]);
                    $dfront["C"] = array_values($depthChart["RDT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["DE"]);
                    $LBs["weak"] = array_values($depthChart["SLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    break;
                case "CHI":
                    $dfront["LT"] = array_values($depthChart["LOLB"]);
                    $dfront["LG"] = array_values($depthChart["DT"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["DE"]);
                    $dfront["RT"] = array_values($depthChart["ROLB"]);
                    $LBs["middle"] = array_values($depthChart["RILB"]);
                    $LBs["weak"] = array_values($depthChart["LILB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "NYJ":
                    $dfront["LT"] = array_values($depthChart["LOLB"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["ROLB"]);
                    $LBs["middle"] = array_values($depthChart["RILB"]);
                    $LBs["weak"] = array_values($depthChart["LILB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "MIA":
                    $dfront["LT"] = array_values($depthChart["LOLB"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["DE"]);
                    $dfront["RT"] = array_values($depthChart["LOLB"]);
                    $LBs["middle"][] = $depthChart["LB"][1];
                    $LBs["weak"][] = $depthChart["LB"][2];
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "MIN":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["DT"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["WLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["SLB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "LAR":
                    $dfront["LT"] = array_values($depthChart["WILL"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["DT"]);
                    $dfront["RT"] = array_values($depthChart["OLB"]);
                    $LBs["middle"] = array_values($depthChart["LILB"]);
                    $LBs["weak"] = array_values($depthChart["RILB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "CIN":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["NT"]);
                    $dfront["C"] = array_values($depthChart["RDT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["SLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["SLB"]);
                    //$cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "NYG":
                    $dfront["LT"] = array_values($depthChart["WLB"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["SAM"]);
                    $LBs["middle"] = array_values($depthChart["SILB"]);
                    $LBs["weak"] = array_values($depthChart["RILB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "DAL":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["LDT"]);
                    $dfront["C"] = array_values($depthChart["RDT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["SLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["WLB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "NE":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["DT"]);
                    $dfront["C"] = array_values($depthChart["RDE"]);
                    $dfront["RG"] = array_values($depthChart["LB"]);
                    $dfront["RT"][] = $depthChart["LDE"][2];
                    $LBs["middle"][] =$depthChart["FS"][2];
                    $LBs["weak"][] = $depthChart["LB"][2];
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "BAL":
                    $dfront["LT"] = array_values($depthChart["RUSH"]);
                    $dfront["LG"] = array_values($depthChart["DT"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["DE"]);
                    $dfront["RT"] = array_values($depthChart["SLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["WLB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "LAC":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["DT"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["DE/OB"]);
                    $dfront["RT"] = array_values($depthChart["ROLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["LOLB"]);
                    $cb["slot"] = $depthChart["NB"];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "GB":
                    $dfront["LT"] = array_values($depthChart["LOLB"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["ROLB"]);
                    $LBs["middle"][] = $depthChart["LILB"][1];
                    $LBs["weak"][] = $depthChart["LILB"][2];
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "CLE":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["LDT"]);
                    $dfront["C"] = array_values($depthChart["RDT"]);
                    $dfront["RG"] = array_values($depthChart["DE"]);
                    $dfront["RT"] = array_values($depthChart["SAM"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["WLB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "DEN":
                    $dfront["LT"] = array_values($depthChart["WLB"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["SLB"]);
                    $LBs["middle"] = array_values($depthChart["LILB"]);
                    $LBs["weak"] = array_values($depthChart["RILB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "TB":
                    $dfront["LT"] = array_values($depthChart["LOLB"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["ROLB"]);
                    $LBs["middle"] = array_values($depthChart["LILB"]);
                    $LBs["weak"] = array_values($depthChart["RILB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "SEA":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["LDT"]);
                    $dfront["C"] = array_values($depthChart["RDT"]);
                    $dfront["RG"] = array_values($depthChart["LEO"]);
                    $dfront["RT"] = array_values($depthChart["SLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["ROLB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "DET":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["LDT"]);
                    $dfront["C"] = array_values($depthChart["RDT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["ROLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["LOLB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "OAK":
                    $dfront["LT"][] = $depthChart["LDE"][2];
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["LDT"]);
                    $dfront["RG"] = array_values($depthChart["RDT"]);
                    $dfront["RT"] = array_values($depthChart["RDE"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["WLB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "IND":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["NT"]);
                    $dfront["C"] = array_values($depthChart["UT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["SLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["WLB"]);
                    $cb["slot"] = array_values($depthChart["NB"]);
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "PIT":
                    $dfront["LT"] = array_values($depthChart["LOLB"]);
                    $dfront["LG"] = array_values($depthChart["DT"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["DE"]);
                    $dfront["RT"] = array_values($depthChart["ROLB"]);
                    $LBs["middle"] = array_values($depthChart["RILB"]);
                    $LBs["weak"] = array_values($depthChart["LILB"]);
                    $cb["slot"] = array_values($depthChart["NB"]);
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "PHI":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["LDT"]);
                    $dfront["C"] = array_values($depthChart["RDT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["ROLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["LOLB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "CAR":
                    $dfront["LT"] = array_values($depthChart["ROLB"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["LOLB"]);
                    $LBs["middle"] = array_values($depthChart["RILB"]);
                    $LBs["weak"] = array_values($depthChart["LILB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "TEN":
                    $dfront["LT"] = array_values($depthChart["LOLB"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["DT"]);
                    $dfront["RG"] = array_values($depthChart["NT"]);
                    $dfront["RT"] = array_values($depthChart["ROLB"]);
                    $LBs["middle"] = array_values($depthChart["RILB"]);
                    $LBs["weak"] = array_values($depthChart["LILB"]);
                    $cb["slot"] = $depthChart["RCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "BUF":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["LDT"]);
                    $dfront["C"] = array_values($depthChart["RDT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["WLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["SLB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "WAS":
                    $dfront["LT"] = array_values($depthChart["WLB"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["SLB"]);
                    $LBs["middle"][] = $depthChart["MLB"][1];
                    $LBs["weak"][] = $depthChart["MLB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "HOU":
                    $dfront["LT"] = array_values($depthChart["JACK"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["SLB"]);
                    $LBs["middle"] = array_values($depthChart["MIKE"]);
                    $LBs["weak"] = array_values($depthChart["WILL"]);
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "JAX":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["DT"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"][] = array_values($depthChart["LDE"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["WLB"]);
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "SF":
                    $dfront["LT"] = array_values($depthChart["LDE"]);
                    $dfront["LG"] = array_values($depthChart["LDT"]);
                    $dfront["C"] = array_values($depthChart["RDT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"][] = $depthChart["LDE"][2];
                    $LBs["middle"] = array_values($depthChart["MIKE"]);
                    $LBs["weak"] = array_values($depthChart["WILL"]);
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "ARI":
                    $dfront["LT"] = array_values($depthChart["WILL"]);
                    $dfront["LG"] = array_values($depthChart["LDE"]);
                    $dfront["C"] = array_values($depthChart["NT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["SLB"]);
                    $LBs["middle"] = array_values($depthChart["MLB"]);
                    $LBs["weak"] = array_values($depthChart["SLB"]);
                    $cb["slot"] = $depthChart["LCB"][2];
//                    $LBs["strong"][] = array_values($depthChart["SLB"][2]);
                    break;
                case "KC":
                    $dfront["LT"][] = $depthChart["RDE"][2];
                    $dfront["LG"] = array_values($depthChart["NT"]);
                    $dfront["C"] = array_values($depthChart["DT"]);
                    $dfront["RG"] = array_values($depthChart["RDE"]);
                    $dfront["RT"] = array_values($depthChart["RDE"]);
                    $LBs["middle"][] = $depthChart["LB"][1];
                    $LBs["weak"][] = $depthChart["LB"][2];
//                    $LBs["strong"][] = array_values($depthChart["LB"][3]);
                    break;
                default:
                    if ($defScheme == "4-3") {
                        $dfront["LT"] = array_values($dfront["LDE"]);
                        $dfront["LG"] = array_values($dfront["LDT"]);
                        $dfront["C"] = array_values($dfront["RDT"]);
                        $dfront["RG"] = array_values($dfront["RDE"]);
                        $dfront["RT"] = array_values($dfront["ROLB"]);
                        $passFront["LT"] = array_values($depthChart["LDE"]);
                        $passFront["LG"] = array_values($depthChart["DT"]);
                        $passFront["RG"] = array_values($depthChart["RDE"]);
                        $passFront["RT"] = array_values($depthChart["ROLB"]);
                        $LBs["weak"] = array_values($depthChart["LOLB"]);
                        $LBs["middle"] = array_values($depthChart["MLB"]);
//                        $LBs["strong"] = array_values($depthChart["ROLB"]);
                        break;
                    } else {
                        $dfront["LT"] = array_values($dfront["LOLB"]);
                        $dfront["LG"] = array_values($dfront["LDE"]);
                        $dfront["C"] = array_values($dfront["NT"]);
                        $dfront["RG"] = array_values($dfront["RDT"]);
                        $dfront["RT"] = array_values($dfront["ROLB"]);
                        $passFront["LT"] = array_values($depthChart["LDE"]);
                        $passFront["LG"] = array_values($depthChart["DT"]);
                        $passFront["RG"] = array_values($depthChart["RDE"]);
                        $passFront["RT"] = array_values($depthChart["ROLB"]);
                        $LBs["weak"] = array_values($depthChart["LOLB"]);
                        $LBs["middle"] = array_values($depthChart["MLB"]);
//                        $LBs["strong"] = array_values($depthChart["ROLB"]);
                        break;
                    }

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

            $breakpoint = true;

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

            //get weak LB
            if (array_key_exists("SWR", $depthChart)) {
                $jsVars['WR']['slot'] = array_shift($depthChart["NB"]);
            }

            if (!array_key_exists("SWR", $depthChart)) {
                $team->depth_chart["SWR"][1] = $depthChart["LWR"][2];
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
            'team2' => $jsVars['team2'],
            'week' => $matchup->week,
        ]);

        return $viewModel;
    }
}