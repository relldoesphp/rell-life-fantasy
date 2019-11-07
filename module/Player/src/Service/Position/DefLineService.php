<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/7/19
 * Time: 4:38 PM
 */

namespace Player\Service\Position;


use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\ProgressBar\Adapter\Console;
use Zend\ProgressBar\ProgressBar;
use Zend\Db\Sql\Select;
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Dom\Query;

class DefLineService extends ServiceAbstract
{
    private $consoleAdapter;
    private $repository;
    private $command;
    private $db;

    public $specialMetrics = [
        'runStuff' => [
            'field' => 'metrics',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'passRush' => [
            'field' => 'metrics',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'edgeRush' => [
            'field' => 'metrics',
            'sort' => 'ASC',
            'lpad' => 6
        ]
    ];

    public function __construct(AdapterInterface $db, Console $consoleAdapter, PlayerCommandInterface $command, PlayerRepositoryInterface $repository)
    {
        parent::__construct($db, $consoleAdapter, $command, $repository);
        $this->repository = $repository;
        $this->command = $command;
        $this->consoleAdapter = $consoleAdapter;
    }

    public function calculateMetrics()
    {
        parent::calculateMetrics("DL");
    }

    public function calculatePercentiles()
    {
        parent::calculatePercentiles("NT", $this->percentileMetrics);
        parent::calculatePercentiles("DT", $this->percentileMetrics);
        parent::calculatePercentiles("DE", $this->percentileMetrics);
    }

    public function calculateSpecialPercentiles()
    {
        parent::calculatePercentiles("DL", $this->specialMetrics);
    }

    public function calculateSpecialScores()
    {
        $dls = $this->repository->findAllPlayers("DL");
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($dls));
        $pointer = 0;
        foreach ($dls as $dl) {

            $dl->decodeJson();
            if (empty($dl->getMetrics())) {
                continue;
            }

            $info = $dl->getPlayerInfo();
            $metrics = $dl->getMetrics();
            $percentiles = $dl->getPercentiles();

            /*** Calculate Run Block ***/
            $metrics['runStuff'] = null;
            if (!array_key_exists('benchPress', $metrics)) {
                $found = true;
            }
            if (!in_array($metrics['benchPress'], [null, "-", "", "null"])
                && !in_array($metrics['broadJump'], [null, "-", "", "null"])) {
                $metrics['runStuff'] = round(($percentiles['bully'] * .30) + ($percentiles['power'] * .70), 2);
            }

            if (in_array($metrics['benchPress'], [null, "-", "", "null"])
                && !in_array($metrics['broadJump'], [null, "-", "", "null"])) {
                $metrics['runStuff'] = $percentiles['power'];
            }

            if (!in_array($metrics['benchPress'], [null, "-", "", "null"])
                && in_array($metrics['broadJump'], [null, "-", "", "null"])) {
                $metrics['runStuff'] = $percentiles['benchPress'];
            }

            /*** Calculate Pass Block ***/
            $metrics['passRush'] = $metrics['runStuff'];
            if (!in_array($metrics['shuttle'], [null, "-", "", "null"])
                && !in_array($metrics['benchPress'], [null, "-", "", "null"])) {
                $metrics['passRush'] = round(($percentiles['bully'] * .30) + ($percentiles['speedScore'] * .50) + ($percentiles['shuttle'] * .20), 2);
            }

            if (in_array($metrics['shuttle'], [null, "-", "", "null"])
                && !in_array($metrics['benchPress'], [null, "-", "", "null"])) {
                $metrics['passRush'] = round(($percentiles['bully'] * .40) + ($percentiles['speedScore'] * .60),2);
            }

            /*** Calculate Edge Block ***/
            $metrics['edgeRush'] = null;
            if (!in_array($metrics['benchPress'], [null, "-", "", "null"])
                && !in_array($metrics['fortyTime'], [null, "-", "", "null"])) {
                $metrics['edgeRush'] = round(($percentiles['bully'] * .25) + ($percentiles['speedScore'] * .60) + ($percentiles['armsInches'] * .15),2);
            } else {
                $metrics['edgeRush'] = $metrics['passRush'];
            }

            $dl->setMetrics($metrics);

            $this->command->save($dl);
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }
}
