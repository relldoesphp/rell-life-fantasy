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
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class ScriptController extends AbstractActionController
{
    private $command;
    private $wrCommand;
    private $rbCommand;
    private $teCommand;

    public function __construct(PlayerCommandInterface $command)
    {
        $this->command = $command;
        $this->wrCommand = $command->getWrCommand();
        $this->rbCommand = $command->getRbCommand();
        $this->teCommand = $command->getTeCommand();
    }

    public function indexAction()
    {
        return parent::indexAction(); // TODO: Change the autogenerated stub
    }

    public function updateWrMetricsAction()
    {
        $wr = "WR";
//        $this->wrCommand->calculateMetrics($wr);
//        $this->wrCommand->calculatePercentiles($wr);
        $this->wrCommand->calculateSpecialScores($wr);
        $this->wrCommand->calculatePercentiles($wr);
    }

    public function updateRbMetricsAction()
    {
        $wr = "RB";
 //       $this->rbCommand->calculateMetrics($wr);
 //       $this->rbCommand->calculatePercentiles($wr);
        $this->rbCommand->calculateSpecialScores($wr);
    }

    public function updateTeMetricsAction()
    {
        $wr = "TE";
        $this->teCommand->calculateMetrics($wr);
        $this->teCommand->calculatePercentiles($wr);
    }

    public function dataScrapperAction()
    {
        $this->teCommand->scrapCollegeJob();
    }
}