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

        $team1 = $this->teamManager->getTeam($team);

        //build offensive line
        $team1->decodeJson();
        $depthChart = $team1->depth_chart;
        foreach ($depthChart as $position => $players) {
            foreach ($players as $depth => $player) {
                if (array_key_exists("injury_info", $player) && !in_array($player["injury_info"]["injury_status"], ["OUT", "IR"])) {
                    if (in_array($position, ["LT", "LG", "C", "RG", "RT"])) {
                        $jsVars['OL'][$position] = $player;
                    }

                    if (in_array($position, ["LWR", "SWR", "RWR", "TE"])) {
                        $jsVars['WR'][$position] = $player;
                    }

                    if ($position == "RB" && in_array($depth,[1,2])) {
                        $jsVars['RB'][$position][$depth] = $player;
                    }

                    if ($position == "QB" && $depth == 1) {
                        $jsVars['QB'][$position] = $player;
                    }
                    break;
                } else {
                    continue;
                }
            }
        }

        $def = $this->params()->fromQuery("def");
        if (!empty($def)) {
            $team2 = $this->teamManager->getTeam($def);
            //build offensive line
            $team2->decodeJson();
            $depthChart = $team2->depth_chart;
            $defScheme = "3-4";
            foreach ($depthChart as $position => $players) {
                foreach ($players as $depth => $player) {
                    if (array_key_exists("injury_info", $player) && !in_array($player["injury_info"]["injury_status"], ["OUT", "IR"])) {
                        if ($defScheme == "3-4" && in_array($position, ["NT", "RDE", "LDE"])) {
                            if ($depth == 1) {
                                $jsVars['DL'][$position] = $player;
                            }
                        }

                        if ($defScheme == "3-4" && in_array($position, ["LILB", "RILB", "ROLB", "LOLB"])) {
                            $jsVars['LB'][$position] = $player;
                        }


                        if ($defScheme == "4-3" && in_array($position, ["LDT", "RDT", "LDE", "RDE"])) {
                            $jsVars['DL'][$position] = $player;
                        }

                        if ($defScheme == "4-3" && in_array($position, ["MLB", "SLB", "WLB"])) {
                            $jsVars['LB'][$position] = $player;
                        }


                        if (in_array($position, ["LCB", "RCB"]) && in_array($depth,[1,2,3])) {
                            $jsVars['CB'][$position][$depth] = $player;
                        }
                        break;
                    } else {
                        continue;
                    }
                }
            }
        }




        $viewModel = new ViewModel([
            'jsVars' => $jsVars,
        ]);

        return $viewModel;
    }

    public function depthChartAction(){

    }
}