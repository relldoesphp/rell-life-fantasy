<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/10/19
 * Time: 9:38 PM
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

class QbService extends ServiceAbstract
{

    public $specialMetrics = [
        'throwVelocity' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'armTalent' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'playmaker' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'mobility' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'depthAdjPct' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'bestYpa' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'bestPct' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ]
    ];

    public $repository;
    public $command;
    public $consoleAdapter;

    public function __construct(AdapterInterface $db, Console $consoleAdapter, PlayerCommandInterface $command, PlayerRepositoryInterface $repository)
    {
        parent::__construct($db, $consoleAdapter, $command, $repository);
        $this->repository = $repository;
        $this->command = $command;
        $this->consoleAdapter = $consoleAdapter;
    }

    public function calculateMetrics($type)
    {
        parent::calculateMetrics("QB"); // TODO: Change the autogenerated stub
    }

    public function calculatePercentiles($type = "", $array = [])
    {
        parent::calculatePercentiles("QB", $this->percentileMetrics); // TODO: Change the autogenerated stub
    }

    public function calculateSpecialPercentiles()
    {
        parent::calculatePercentiles("QB", $this->specialMetrics);
    }

    public function calculateSpecialScores()
    {
        $qbs = $this->repository->findAllPlayers("QB");
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($qbs));
        $pointer = 0;
        foreach ($qbs as $qb) {

            if ($qb->getId() == 2678) {
                $got = true;
            }
            $qb->decodeJson();

            $info = $qb->getPlayerInfo();
            $metrics = $qb->getMetrics();
            $percentiles = $qb->getPercentiles();
            $college = $qb->getCollegeStats();

            if ($college != null) {
                $bestYds = 0;
                $bestYear = 0;
                foreach ($college as $year) {
                    if ($year['ypa'] > 0) {
                        $metrics["depthAdjPct"] = $year['pct'] + round(7.85 * ($year['ypa'] - 8.40), 2);
                        $metrics["bestPct"] = $year['pct'];
                        $metrics["bestYpa"] = $year['ypa'];
                    }
                }
            } else {
                $metrics["bestPct"] = null;
                $metrics["bestYpa"] = null;
                $metrics["depthAdjPct"] = "";
            }

            if (array_key_exists('throwVelocity', $metrics)) {
                $metrics["throwVelocity"] = str_replace( " mph", "", $metrics['throwVelocity']);
            } else {
                $metrics["throwVelocity"] = "";
            }


            if ($metrics['throwVelocity'] == "") {
                $throwVelocity = 50;
            } else {
                $throwVelocity = $percentiles['throwVelocity'];
            }

            if ($metrics['depthAdjPct'] == "") {
                $depthAdjPct = 50;
            } else {
                $depthAdjPct = $percentiles['depthAdjPct'];
            }


            $metrics['armTalent'] = ($throwVelocity * .50) + ($depthAdjPct * .50);

            $metrics['mobility'] = 40;

            if ($percentiles['elusiveness'] != "" && $percentiles['power'] != "" && $percentiles['fortyTime'] != "") {
                $metrics["mobility"] = ($percentiles['elusiveness'] * .30) + ($percentiles['power'] * .30) + ($percentiles['fortyTime'] * .40);
            }

            if ($percentiles['elusiveness'] == "" && $percentiles['power'] != "" && $percentiles['fortyTime'] != "") {
                $metrics["mobility"] = ($percentiles['power'] * .30) + ($percentiles['fortyTime'] * .70);
            }

            if ($percentiles['elusiveness'] == "" && $percentiles['power'] == "" && $percentiles['fortyTime'] != "") {
                $metrics["mobility"] = $percentiles['fortyTime'];
            }

            if ($percentiles['elusiveness'] != "" && $percentiles['power'] != "" && $percentiles['fortyTime'] == "") {
                $metrics["mobility"] = ($percentiles['elusiveness'] * .60) + ($percentiles['power'] * .40);
            }

            $metrics['playmaker'] = ($metrics['armTalent'] + $metrics['mobility'])/2;

            $qb->setMetrics($metrics);

            $this->command->save($qb);

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }
}