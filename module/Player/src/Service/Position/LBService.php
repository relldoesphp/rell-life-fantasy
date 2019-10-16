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

class LBService extends ServiceAbstract
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
        parent::calculateMetrics("OLB");
        parent::calculateMetrics("ILB");
    }

    public function calculatePercentiles()
    {
        parent::calculatePercentiles("OLB", $this->percentileMetrics);
        parent::calculatePercentiles("ILB", $this->percentileMetrics);
    }

    public function calculateSpecialPercentiles()
    {
        parent::calculatePercentiles("OLB", $this->specialMetrics);
        parent::calculatePercentiles("ILB", $this->specialMetrics);
    }

    public function calculateSpecialScores()
    {
        $lbs = [
            "OLB",
            "ILB"
        ];

        foreach ($lbs as $type) {
            $dls = $this->repository->findAllPlayers($type);
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

                $metrics['passRush'] = $metrics['runStuff'];


                /************* Calculate Pass Block ***************/

                /** 1-tech **/
                $metrics['1Tech'] = $metrics['runStuff'];
                if (!in_array($metrics['fortyTime'], [null, "-", "", "null"])
                    && !in_array($metrics['benchPress'], [null, "-", "", "null"])
                    && !in_array($metrics['broadJump'], [null, "-", "", "null"])
                ) {
                    $metrics['1Tech'] = round(($percentiles['bully'] * .30) + ($percentiles['power'] * .50) + ($percentiles['speedScore'] * .20), 2);
                }

                if (in_array($metrics['shuttle'], [null, "-", "", "null"])
                    && !in_array($metrics['benchPress'], [null, "-", "", "null"])) {
                    $metrics['1Tech'] = round(($percentiles['bully'] * .40) + ($percentiles['speedScore'] * .60),2);
                }

                /** 3-tech **/
                if (!in_array($metrics['shuttle'], [null, "-", "", "null"])
                    && !in_array($metrics['benchPress'], [null, "-", "", "null"])) {
                    $metrics['3Tech'] = round(($percentiles['bully'] * .30) + ($percentiles['speedScore'] * .50) + ($percentiles['shuttle'] * .20), 2);
                }

                if (in_array($metrics['shuttle'], [null, "-", "", "null"])
                    && !in_array($metrics['benchPress'], [null, "-", "", "null"])) {
                    $metrics['3Tech'] = round(($percentiles['bully'] * .40) + ($percentiles['speedScore'] * .60),2);
                }

                /** 7-tech **/
                $metrics['7Tech'] = null;
                if (!in_array($metrics['benchPress'], [null, "-", "", "null"])
                    && !in_array($metrics['fortyTime'], [null, "-", "", "null"])) {
                    $metrics['7Tech'] = round(($percentiles['speedScore'] * .25) + ($percentiles['power'] * .45)+ ($percentiles['bully'] * .5) + ($percentiles['heightInches'] * .15),2);
                } else {
                    $metrics['7Tech'] = $metrics['passRush'];
                }

                /** 9-tech **/
                $metrics['9Tech'] = null;
                if (!in_array($metrics['shuttle'], [null, "-", "", "null"])
                    && !in_array($metrics['fortyTime'], [null, "-", "", "null"])) {
                    $metrics['9Tech'] = round(($percentiles['power'] * .15) + ($percentiles['fortyTime'] * .50)+ ($percentiles['shuttle'] * .35),2);
                } else {
                    $metrics['9Tech'] = $metrics['passRush'];
                }


                $dl->setMetrics($metrics);

                $this->command->save($dl);
                $pointer++;
                $progressBar->update($pointer);
            }
            $progressBar->finish();
        }
    }
}
