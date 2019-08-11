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

    public function calculatePercentiles($type, $array)
    {
        parent::calculatePercentiles("QB", $this->percentileMetrics); // TODO: Change the autogenerated stub
    }

    public function calculateSpecialPercentiles()
    {
        parent::calculatePercentiles("QB", $this->specialMetrics);
    }

    public function calculateSpecialScores()
    {
        $qbs = $this->repository->findAllPlayers("RB");
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($qbs));
        $pointer = 0;
        foreach ($qbs as $qb) {
            $qb->decodeJson();

            /*
             *     $info = json_decode($te['player_info']);
            $metrics = json_decode($te['metrics']);
            $percentiles = json_decode($te['percentiles']);
            $college = json_decode($te['college_stats']);

            if ($college != null) {
                $bestYds = 0;
                $bestYear = 0;
                foreach ($college as $year) {
                    if ($year->ypa > 0) {
                        $data["depthAdjPct"] = round(($year->ypa * 7.85), 2);
                        $data["bestPct"] = $year->pct;
                        $data["bestYpa"] = $year->ypa;
                    }
                }
            } else {
                $data["bestPct"] = null;
                $data["bestYpa"] = null;
                $data["depthAdjPct"] = "";
            }


           $data["throwVelocity"] = str_replace( " mph", "", $metrics->throwVelocity);

            if ($data['throwVelocity'] == "") {
                $throwVelocity = 50;
            } else {
                $throwVelocity = $percentiles->throwVelocity;
            }

            if ($data['depthAdjPct'] == "") {
                $depthAdjPct = 50;
            } else {
                $depthAdjPct = $percentiles->depthAdjPct;
            }

            $data['armTalent'] = ($throwVelocity * .50) + ($depthAdjPct * .50);

            $data["mobility"] = ($percentiles->elusiveness * .30) + ($percentiles->power * .30) + ($percentiles->fortyTime * .40);

            $data['playmaker'] = ($data['armTalent'] + $data['mobility'])/2;
             *
             */

            $this->command->save($qb);

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }
}