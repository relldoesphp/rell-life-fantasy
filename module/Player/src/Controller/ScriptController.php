<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/22/19
 * Time: 8:46 AM
 */

namespace Player\Controller;

use Player\Model\Player;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Player\Service\PlayerManager;
use Player\Service\StatsManager;
use Player\Service\TeamManager;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;


class ScriptController extends AbstractActionController
{
    private $command;
    private $repository;
    private $playerManager;
    private $statsManager;
    private $teamManager;

    public function __construct(
        PlayerCommandInterface $command,
        PlayerRepositoryInterface $repository,
        PlayerManager $playerManager,
        StatsManager $statsManager,
        TeamManager $teamManager
    )
    {
        $this->command = $command;
        $this->repository = $repository;
        $this->playerManager = $playerManager;
        $this->statsManager = $statsManager;
        $this->teamManager = $teamManager;
    }

    public function indexAction()
    {
        return parent::indexAction(); // TODO: Change the autogenerated stub
    }

    public function updateWrMetricsAction()
    {
        $this->playerManager->updateWrMetrics();
    }

    public function updateRbMetricsAction()
    {
        $this->playerManager->updateRbMetrics();
    }

    public function updateTeMetricsAction()
    {
        $this->playerManager->updateTeMetrics();
    }

    public function updateQbMetricsAction()
    {
        $this->playerManager->updateQbMetrics();
    }

    public function updateOLMetricsAction()
    {
        $this->playerManager->updateWrMetrics();
    }

    public function dataScrapperAction()
    {

        $this->playerManager->scrapCollegeJob();
    }

    public function getSleeperStatsAction()
    {
        $this->statsManager->getSeasonStats('2020');
        $positions = ["WR", "RB", "TE", "QB"];
        foreach($positions as $position) {
            $this->statsManager->makeSeasonAverages($position, "2020");
            $this->statsManager->makeSeasonRanks($position, "2020");
        }
    }

    public function makeNameJsonAction()
    {
       $json = $this->repository->getPlayerNames('Off');
       file_put_contents('public/data/names.json', json_encode($json));
    }

    public function updateSleeperInfoAction()
    {
       // $this->playerManager->updateSleeperInfo();
        $this->playerManager->syncSisIds();
    }

    public function getSleeperLogsAction()
    {
        $this->statsManager->getGameLogs("2020");
        $this->statsManager->makeGameLogRanks("RB", "2020");
        $this->statsManager->makeGameLogRanks("TE", "2020");
        $this->statsManager->makeGameLogRanks("QB", "2020");
        $this->statsManager->makeGameLogRanks("WR", "2020");
//
//        $this->statsManager->getSleeperGameLogs("2017");
//        $this->statsManager->makeGameLogRanks("QB", "2017");
//        $this->statsManager->makeGameLogRanks("RB", "2017");
//        $this->statsManager->makeGameLogRanks("TE", "2017");
//        $this->statsManager->makeGameLogRanks("WR", "2017");

//        $this->statsManager->getSleeperGameLogs("2016");
//        $this->statsManager->makeGameLogRanks("QB", "2016");
//        $this->statsManager->makeGameLogRanks("RB", "2016");
//        $this->statsManager->makeGameLogRanks("TE", "2016");
//        $this->statsManager->makeGameLogRanks("QB", "2016");
    }

    public function buildDepthChartsAction()
    {
       $team = $this->teamManager->buildDepthCharts();
    }

    public function updateTeamsAction()
    {
        $this->teamManager->sync();
    }
}