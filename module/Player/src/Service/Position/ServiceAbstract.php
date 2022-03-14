<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/4/19
 * Time: 8:03 PM
 */

namespace Player\Service\Position;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Json\Json;
use Laminas\ProgressBar\Adapter\Console;
use Laminas\Http\Request;
use Laminas\Http\Client;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Laminas\ProgressBar\ProgressBar;
use Player\Service\SportsInfoApi;


class ServiceAbstract
{
    private $db;
    private $consoleAdapter;
    private $command;
    private $repository;
    private $sisApi;

    public $percentileMetrics = [
        'heightInches' => [
            'field' => 'player_info',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'armsInches' => [
            'field' => 'player_info',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'weight' => [
            'field' => 'player_info',
            'sort' => 'ASC',
            'lpad' => 3
        ],
        'bmi' => [
            'field' => 'player_info',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'hands' => [
            'field' => 'player_info',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'fortyTime' => [
            'field' => 'metrics',
            'sort' => 'DESC',
            'lpad' => 6
        ],
        'cone' => [
            'field' => 'metrics',
            'sort' => 'DESC',
            'lpad' => 6
        ],
        'shuttle' => [
            'field' => 'metrics',
            'sort' => 'DESC',
            'lpad' => 6
        ],
        'benchPress' => [
            'field' => 'metrics',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'verticalJump' => [
            'field' => 'metrics',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'broadJump' => [
            'field' => 'metrics',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'agility' => [
            'field' => 'metrics',
            'sort' => 'DESC',
            'lpad' => 6
        ],
        'routeAgility' => [
            'field' => 'metrics',
            'sort' => 'DESC',
            'lpad' => 6
        ],
        'jukeAgility' => [
            'field' => 'metrics',
            'sort' => 'DESC',
            'lpad' => 6
        ],
        'elusiveness' => [
            'field' => 'metrics',
            'sort' => 'DESC',
            'lpad' => 6
        ],
        'jumpball' => [
            'field' => 'metrics',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'bully' => [
            'field' => 'metrics',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'power' => [
            'field' => 'metrics',
            'sort' => 'ASC',
            'lpad' => 6
        ],
        'speedScore' => [
            'field' => 'metrics',
            'sort' => 'ASC',
            'lpad' => 6
        ]
    ];

    public function __construct(
        AdapterInterface $db,
        Console $consoleAdapter,
        PlayerCommandInterface $command,
        PlayerRepositoryInterface $repository,
        SportsInfoApi $sisApi
    )
    {
        $this->repository = $repository;
        $this->consoleAdapter = $consoleAdapter;
        $this->command = $command;
        $this->repository = $repository;
        $this->sisApi = $sisApi;
    }

    public function calculateMetrics($type)
    {
        $posInfo = [
            'QB' => [
                'bmiAvg' => 27.77,
                'weightAvg' => 220.1,
                'heightAvg' => 75.04,
                'benchAvg' => 18.11,
                'broadAvg' => 111.11,
                'agilityAvg' => 7.12 + 4.34,
                'coneAvg' => 7.13,
                'shuttleAvg' => 4.34,
            ],
            'RB' => [
                'bmiAvg' => 30.27,
                'weightAvg' => 210.94,
                'heightAvg' => 70.57,
                'benchAvg' => 18.11,
                'broadAvg' => 113.43,
                'agilityAvg' => 11.21,
                'coneAvg' => 7.06,
                'shuttleAvg' => 4.31,
            ],
            'WR' => [
                'bmiAvg' => 26.6,
                'weightAvg' => 196.08,
                'heightAvg' => 72.6,
                'benchAvg' => 12.29,
                'broadAvg' => 116.39,
                'agilityAvg' => 11.14,
                'coneAvg' => 6.95,
                'shuttleAvg' => 4.25,
            ],
            'TE' => [
                'bmiAvg' => 30.22,
                'weightAvg' => 248.48,
                'heightAvg' => 76.46,
                'benchAvg' => 17.6,
                'broadAvg' => 116.83,
                'agilityAvg' => 11.47,
                'coneAvg' => 7.16,
                'shuttleAvg' => 4.40,
            ],
            'OL' => [
                'bmiAvg' => 37.12,
                'weightAvg' => 309.23,
                'heightAvg' => 76.71,
                'benchAvg' => 22.05,
                'broadAvg' => 104.25,
                'agilityAvg' => 6.51 + 4.05,
                'coneAvg' => 7.12,
                'shuttleAvg' => 4.34,
            ],
            'DL' => [
                'bmiAvg' => 35.37,
                'weightAvg' => 282.59,
                'heightAvg' => 75.48,
                'benchAvg' => 23.3,
                'broadAvg' => 111.54,
                'agilityAvg' => 6.69 + 4.14,
                'coneAvg' => 7.12,
                'shuttleAvg' => 4.34,
            ],
            'OLB' => [
                'bmiAvg' => 31.13,
                'weightAvg' => 241.15,
                'heightAvg' => 74.28,
                'benchAvg' => 20.36,
                'broadAvg' => 119.17,
                'agilityAvg' => 6.69 + 4.14,
                'coneAvg' => 7.12,
                'shuttleAvg' => 4.34,
            ],
            'ILB' => [
                'bmiAvg' => 31.04,
                'weightAvg' => 236.36,
                'heightAvg' => 73.29,
                'benchAvg' => 23.3,
                'broadAvg' => 111.54,
                'agilityAvg' => 6.69 + 4.14,
                'coneAvg' => 7.12,
                'shuttleAvg' => 4.34,
            ],
            'CB' => [
                'bmiAvg' => 26.58,
                'weightAvg' => 191.2,
                'heightAvg' => 71.42,
                'benchAvg' => 23.3,
                'broadAvg' => 111.54,
                'agilityAvg' => 6.69 + 4.22,
                'coneAvg' => 6.94,
                'shuttleAvg' => 4.22,
            ],
            'FS' => [
                'bmiAvg' => 27.57,
                'weightAvg' => 202.22,
                'heightAvg' => 72.04,
                'benchAvg' => 23.3,
                'broadAvg' => 111.54,
                'agilityAvg' => 6.69 + 4.14,
                'coneAvg' => 7.12,
                'shuttleAvg' => 4.34,
            ],
            'SS' => [
                'bmiAvg' => 28.14,
                'weightAvg' => 205.05,
                'heightAvg' => 71.59,
                'benchAvg' => 23.3,
                'broadAvg' => 111.54,
                'agilityAvg' => 6.69 + 4.14,
                'coneAvg' => 7.12,
                'shuttleAvg' => 4.34,
            ],
        ];

        $players = $this->repository->findAllPlayers($type);

        print "Metrics started\n";
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($players));
        $pointer = 0;

        foreach ($players as $player) {
            $player->decodeJson();
            $info = $player->getPlayerInfo();
            //make BMI
            // convert from lbs to kg
            if (!empty($info['weight'])
                && array_key_exists('heightInches', $info)
                && !empty($info['heightInches'])) {
                $adjusted_weight = $info['weight'] * 0.45359237;
                // convert from inches to m
                $adjusted_height = $info['heightInches'] * 0.0254;
                // square the height variable
                $adjusted_height_final = $adjusted_height * $adjusted_height;
                // divide the weight by the squared height to get the BMI value
                $prep_bmi = $adjusted_weight/$adjusted_height_final;
                $info['bmi'] = number_format($prep_bmi, 1);
                $player->setPlayerInfo($info);
            }

            //fix height
            if (array_key_exists('height', $info) && $info['height'] > 66 and $info['height'] < 82) {
                $info['heightInches'] = $info['height'];
                $heightArray = [
                    '66' => '5\'6"',
                    '67' => '5\'7"',
                    '68' => '5\'8"',
                    '69' => '5\'9"',
                    '70' => '5\'10"',
                    '71' => '5\'11"',
                    '72' => '6\'0"',
                    '73' => '6\'1"',
                    '74' => '6\'2"',
                    '75' => '6\'3"',
                    '76' => '6\'4"',
                    '77' => '6\'5"',
                    '78' => '6\'6"',
                    '79' => '6\'7"',
                    '80' => '6\'8"',
                    '81' => '6\'9"',
                    '82' => '6\'10"',
                ];
                $info['height'] = $heightArray[$info['height']];
                $player->setPlayerInfo($info);
            }

            if ($player->getTeam() == "Rookie") {
                if (!array_key_exists("hashtag", $info) || empty($info['hashtag']) || $info['hashtag'] == null) {
                    if ($player->getTeam() == "Rookie") {
                        $info["hashtag"] = "#{$player->getFirstName()}{$player->getLastName()}-NFL-Rookie-0";
                        $player->setPlayerInfo($info);
                    }
                }


                $metrics = $player->getMetrics();
                if (array_key_exists("benchPress", $metrics) && $metrics['benchPress'] != null && $metrics['benchPress'] != '-') {
                    $bmiRate = ($posInfo[$type]['benchAvg'])/($posInfo[$type]['bmiAvg']);
                    if (!array_key_exists("bmi", $info)) {
                        $info['bmi'] = $posInfo[$type]['bmiAvg'];
                    }
                    $bmiAdj = $bmiRate * ($info['bmi'] - $posInfo[$type]['bmiAvg']);
                    $metrics['bully'] = round( $bmiAdj + $metrics['benchPress'], 2);
                } else {
                    $metrics['bully'] = null;
                }
//
//                $player->setMetrics($metrics);
//                $player->setPlayerInfo($info);
//                $this->command->save($player);
//                $pointer++;
//                $progressBar->update($pointer);
//                continue;
            }

            $metrics = $player->getMetrics();
            if (empty($metrics)) {
                continue;
            }

            if (!array_key_exists('shuttle', $metrics)) {
                $metrics['shuttle'] = null;
            }

            if (!array_key_exists('cone', $metrics)) {
                $metrics['cone'] = null;
            }

            if (!array_key_exists('broadJump', $metrics)) {
                $metrics['broadJump'] = null;
            }

            if (!array_key_exists('verticalJump', $metrics)) {
                $metrics['verticalJump'] = null;
            }

            if (!array_key_exists('fortyTime', $metrics)) {
                $metrics['fortyTime'] = null;
            }

            $metrics['noTesting'] = false;
            if ($metrics['fortyTime'] == null
                && $metrics['verticalJump'] == null
                && $metrics['broadJump'] == null
                && $metrics['cone'] == null
                && $metrics['shuttle'] == null
            ) {
                $metrics['noTesting'] = true;
            }

            if (in_array($metrics['shuttle'], ["-", "", null])
                && in_array($metrics['cone'], ["-", "", null])) {
                $noAgility = true;
            } else{
                $noAgility = false;
            }

            if (in_array($metrics['verticalJump'], ["-", "", null])
                && in_array($metrics['broadJump'], ["-", "", null])) {
                $noJump = true;
            } else{
                $noJump = false;
            }

            if (in_array($metrics['fortyTime'], ["-", "", null])) {
                $noForty = true;
            } else{
                $noForty = false;
            }

            if (array_key_exists("benchPress", $metrics)) {
                if (in_array($metrics['benchPress'], ["-", "", null])) {
                    $noBench = true;
                } else{
                    $noBench = false;
                }
            }


            if (in_array($metrics['verticalJump'], ["-", "", null])) {
                $noVert = true;
            } else {
                $noVert = false;
            }

            // (average bmi 26.6/ average bench 14.2) = 1.87
            // (1.87 * (wr bmi - average bmi)) + wr bench Press
            if ($type == 'CB') {
                $metrics['benchPress'] = 14;
            }

            if (array_key_exists("benchPress", $metrics) && $metrics['benchPress'] != null && $metrics['benchPress'] != '-') {
                $bmiRate = ($posInfo[$type]['benchAvg'])/($posInfo[$type]['bmiAvg']);
                if (!array_key_exists("bmi", $info)) {
                    $info['bmi'] = $posInfo[$type]['bmiAvg'];
                }
                $bmiAdj = $bmiRate * ($info['bmi'] - $posInfo[$type]['bmiAvg']);
                $metrics['bully'] = round( $bmiAdj + $metrics['benchPress'], 2);
            } else {
                $metrics['bully'] = null;
            }

            if ($metrics['shuttle'] != null && $metrics['shuttle'] != '-' && $metrics['cone'] != null && $metrics['cone'] != "-") {
                $metrics['agility'] = $metrics['shuttle'] + $metrics['cone'];
                $metrics['routeAgility'] = round(($metrics['cone']) + ($metrics['shuttle'] * .15),2);
                $metrics['jukeAgility'] = round(($metrics['shuttle']) + ($metrics['cone'] * .2), 2);
            } else {
                $metrics['agility'] = null;
                $metrics['routeAgility'] = null;
                $metrics['jukeAgility'] = null;
            }

            // If we have a shuttle but no cone, determine estimated cone and use to make route Agility
            if (($metrics['shuttle'] != null && $metrics['shuttle'] != '-') && ($metrics['cone'] == null || $metrics['cone'] == "-")) {
                $coneRate = ($posInfo[$type]['coneAvg'])/($posInfo[$type]['shuttleAvg']);
                $estCone = round($coneRate * $metrics['shuttle'], 2);
                $metrics['estCone'] = $estCone;
                $metrics['jukeAgility'] = round((($metrics['shuttle']) + ($estCone * .2)), 2);
            }

            // If we have a a cone but no shuttle, determine estimated shuttle and use to make juke Agility
            if (($metrics['cone'] != null && $metrics['cone'] != '-') && ($metrics['shuttle'] == null || $metrics['shuttle'] == "-")) {
                $shuttleRate = ($posInfo[$type]['shuttleAvg'])/($posInfo[$type]['coneAvg']);
                $estShuttle = round($shuttleRate * $metrics['cone'], 2);
                $metrics['estShuttle'] = round($estShuttle, 2);
                $metrics['routeAgility'] = round((($metrics['cone']) + ($estShuttle * .15)), 2);
            }

            // Use Juke agility plus BMI to determine how elusive a player is
            if ($metrics['jukeAgility'] != null) {
                $jukeAvg = ($posInfo[$type]['shuttleAvg'] * .8) + ($posInfo[$type]['coneAvg'] * 1.2);
                $elsRate = $jukeAvg/$posInfo[$type]['bmiAvg'];
                $metrics['elusiveness'] = round(($metrics['jukeAgility'] - (($info['bmi'] - $posInfo[$type]['bmiAvg']) * $elsRate)), 2);
            } else {
                $metrics['elusiveness'] = null;
            }

            if ($metrics['routeAgility'] !== null){
                $metrics['routeAgility'] = str_pad($metrics['routeAgility'], 4, '0', STR_PAD_LEFT);
            }

            if ($player->getId() == 2567) {
                $foundHim = true;
            }

            // Adjust broadjump for bmi and weight to determine total power
            if ($metrics['broadJump'] != null && $metrics['broadJump'] !== "-") {
                if ($metrics['verticalJump'] != null && $metrics['verticalJump'] !== "-") {
                    $totalJump = $metrics['verticalJump'] + $metrics['broadJump'];
                } else {
                    $totalJump = round(($metrics['broadJump'] * 1.30),2);
                }
                $weightRate = $posInfo[$type]['broadAvg']/$posInfo[$type]['weightAvg'];
                if (!array_key_exists('weight', $info)) {
                    $found = true;
                }
                $broadPerPound = ($info['weight'] - $posInfo[$type]['weightAvg']) * $weightRate;
//                $heightAdj = $posInfo[$type]['broadAvg']/$posInfo[$type]['heightAvg'];
//                $heightBroad = ($info['heightInches'] - $posInfo[$type]['heightAvg']) * $heightAdj;

                $bmiRate = $posInfo[$type]['broadAvg']/$posInfo[$type]['bmiAvg'];
                $broadPerBmi = $bmiRate * ($info['bmi'] - $posInfo[$type]['bmiAvg']);

                $metrics['power'] = str_pad(round(($totalJump + $broadPerBmi + $broadPerPound),2),0,STR_PAD_LEFT);
            } else {
                $metrics['power'] = null;
            }

            // add jumpball reach, adds bonus for hand size
            if (($metrics['verticalJump'] != null && $metrics['verticalJump'] != '-') && array_key_exists('armsInches', $info)) {
                $metrics['jumpball'] = round($info['heightInches'] + ($info['armsInches'] * 1.1) + ($metrics['verticalJump'] * .8), 2);
                // Premium for big Hands
                if (array_key_exists('hands', $info)){
                    if ($info['hands'] > 9.5) {
                        $metrics['jumpball'] =  $metrics['jumpball'] + 3;
                    }
                    if ($info['hands'] > 9.99) {
                        $metrics['jumpball'] =  $metrics['jumpball'] + 2;
                    }
                }
            } else {
                $metrics['jumpball'] = null;
            }

            //add Weight Speed Score
            if ($metrics['fortyTime'] != null && $metrics['fortyTime'] != "-") {
                $metrics['speedScore'] = round((($info['weight'] * $posInfo[$type]['weightAvg'])/(pow($metrics['fortyTime'],4))), 2);
            } else {
                $metrics['speedScore'] = null;
            }


            if ($metrics == false) {
                $metrics = [];
            }

            $player->setPlayerInfo($info);
            $player->setMetrics($metrics);
            $this->command->save($player);

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Metrics completed\n";
    }

    public function calculatePercentiles($type, $array)
    {
        if (empty($array)) {
            $array = $this->percentileMetrics;
        }

        $percentileArrays = $this->repository->getPercentileRanks($type, $array);
        $players = $this->repository->findAllPlayers($type);
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($players));
        $pointer = 0;
        foreach ($players as $player) {
            $id = $player->getId();
            $player->decodeJson();
            $percentiles = $player->getPercentiles();
            foreach ($percentileArrays as $name => $value) {
                $percentiles[$name] = (array_key_exists($id, $percentileArrays[$name])) ? $percentileArrays[$name][$id] * 100 : "";
            }

//            if ($type == "WR" || $type == "TE") {
//                if ($percentiles['routeAgility'] < $percentiles['jukeAgility']) {
//                    $percentiles['routeAgility'] = $percentiles['jukeAgility'];
//                }
//            }

            $player->setPercentiles($percentiles);
            $this->command->save($player);

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Percentiles completed\n";
    }
}