<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/22/19
 * Time: 8:46 AM
 */

namespace Player\Controller;

use Player\Model\Player;
use Player\Model\PlayerCommandInterface;
use Player\Model\PlayerRepositoryInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class ScriptController extends AbstractActionController
{
    private $command;
    private $repository;
    private $wrCommand;
    private $rbCommand;
    private $teCommand;
    private $olCommand;
    private $dlCommand;
    private $olbCommand;
    private $ilbCommand;
    private $cbCommand;
    private $fsCommand;
    private $ssCommand;


    public function __construct(
        PlayerCommandInterface $command,
        PlayerRepositoryInterface $repository
    )
    {
        $this->command = $command;
        $this->repository = $repository;
        $this->wrCommand = $command->getWrCommand();
        $this->rbCommand = $command->getRbCommand();
        $this->teCommand = $command->getTeCommand();
        $this->olCommand = $command->getOlCommand();
        $this->dlCommand = $command->getDlCommand();
        $this->olbCommand = $command->getOlbCommand();
        $this->ilbCommand = $command->getIlbCommand();
        $this->cbCommand = $command->getCbCommand();
        $this->fsCommand = $command->getFsCommand();
        $this->ssCommand = $command->getSsCommand();
    }

    public function indexAction()
    {
        return parent::indexAction(); // TODO: Change the autogenerated stub
    }

    public function updateWrMetricsAction()
    {
//        $type = "OL";
//        $this->olCommand->calculateMetrics($type);
//        $this->olCommand->calculatePercentiles($type);
        $type = "DL";
        $this->dlCommand->calculateMetrics($type);
        $this->dlCommand->calculatePercentiles($type);
        $type = "OLB";
        $this->olbCommand->calculateMetrics($type);
        $this->olbCommand->calculatePercentiles($type);
        $type = "ILB";
        $this->ilbCommand->calculateMetrics($type);
        $this->ilbCommand->calculatePercentiles($type);
        $type = "CB";
        $this->cbCommand->calculateMetrics($type);
        $this->cbCommand->calculatePercentiles($type);
        $type = "FS";
        $this->fsCommand->calculateMetrics($type);
        $this->fsCommand->calculatePercentiles($type);
        $type = "SS";
        $this->ssCommand->calculateMetrics($type);
        $this->ssCommand->calculatePercentiles($type);

        $type = "WR";
        $this->wrCommand->calculateMetrics($type);
        $this->wrCommand->calculatePercentiles($type);
        $this->wrCommand->calculateSpecialScores($type);
        $this->wrCommand->calculateSpecialPercentiles($type);
    }

    public function updateRbMetricsAction()
    {
        $type = "RB";
        $this->rbCommand->calculateMetrics($type);
        $this->rbCommand->calculatePercentiles($type);
        $this->rbCommand->calculateSpecialScores($type);
        $this->rbCommand->calculatePercentiles($type);
    }

    public function updateTeMetricsAction()
    {
        $type = "TE";
        $this->teCommand->calculateMetrics($type);
        $this->teCommand->calculatePercentiles($type);
//        $this->teCommand->calculateSpecialScores($type);
//        $this->teCommand->calculateSpecialPercentiles($type);
    }

    public function updateOLMetricsAction()
    {
        $type = "OL";
        $this->teCommand->calculateMetrics($type);
        $this->teCommand->calculatePercentiles($type);
//        $this->teCommand->calculateSpecialScores($type);
//        $this->teCommand->calculateSpecialPercentiles($type);
    }

    public function dataScrapperAction()
    {
        $this->rbCommand->scrapCollegeJob();
    }

    public function getSleeperStatsAction()
    {
        $this->command->getSleeperStats();
    }

    public function makeNameJsonAction()
    {
       $json = $this->repository->getPlayerNames();
       file_put_contents('public/data/names.json', json_encode($json));
    }

    public function updateSleeperInfoAction()
    {
        $this->command->updateSleeperInfo();
    }


    public function getSleeperLogsAction()
    {
        $this->command->getSleeperGameLogs();
    }
}