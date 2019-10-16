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
use Zend\ProgressBar\Adapter\Console;
use Zend\ProgressBar\ProgressBar;

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
                if ($teamInfo['position'] == null) {
                    continue;
                }
                //mukbang
                if ($teamInfo['depth_chart_order'] == null) {
                    $depthChart[$teamInfo['position']]['bench'][] = $player->getAllInfo();
                } else {
                    $depthChart[$teamInfo['depth_chart_position']][$teamInfo['depth_chart_order']] = $player->getAllInfo();
                }
            }
            $team->setDepthChart($depthChart);
            $this->teamCommand->saveTeam($team);
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function getTeam($name)
    {
        $team = $this->teamRepository->getTeamByName($name);
        $team->decodeJson();
        return $team;
    }
}