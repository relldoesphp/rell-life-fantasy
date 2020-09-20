<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/15/19
 * Time: 1:11 AM
 */

namespace Player\Controller;
use Player\Service\MatchupManager;
use Player\Service\TeamManager;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;

class TeamController extends AbstractActionController
{
    public $teamManager;
    public $matchupManager;

    public function __construct(TeamManager $teamManager, MatchupManager $matchupManager)
    {
        $this->teamManager = $teamManager;
        $this->matchupManager = $matchupManager;
    }

    public function depthChartAction()
    {

    }

    public function queryAction()
    {
        $query = $this->params()->fromRoute('team', '');
        $results = $this->teamManager->queryTeams($query);
        return new JsonModel($results);
    }
}