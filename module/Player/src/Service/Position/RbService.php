<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/6/19
 * Time: 3:06 PM
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

class RbService extends ServiceAbstract
{
    private $consoleAdapter;
    private $repository;
    private $command;
    private $db;

    public $specialMetrics = [
        'grinder' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'passCatcher' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'alpha' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'collegeScore' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'bestDominator' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'bestRecDominator' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ]
    ];

    public function __construct(
        AdapterInterface $db,
        Console $consoleAdapter,
        PlayerCommandInterface $command,
        PlayerRepositoryInterface $repository)
    {
        parent::__construct($db, $consoleAdapter, $command, $repository);
        $this->repository = $repository;
        $this->command = $command;
        $this->consoleAdapter = $consoleAdapter;
    }

    public function calculateSpecialScores()
    {
        $rbs = $this->repository->findAllPlayers("RB");
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($rbs));
        $pointer = 0;
        foreach ($rbs as $rb) {
            $rb->decodeJson();
            if (empty($rb->getMetrics())) {
                continue;
            }

            $info = $rb->getPlayerInfo();
            $metrics = $rb->getMetrics();
            $percentiles = $rb->getPercentiles();

            $data['receiver'] = 0;
            $data['grinder'] = 0;

            /*** Make Grinder Base ***/
            // 1. We have elusiveness, power, and speedScore
            if ($metrics['elusiveness'] !== null && $metrics['power'] !== null && $metrics['speedScore'] !== null) {
                $data['grinder'] = ($percentiles['power'] * .6) + ($percentiles['elusiveness'] * .2) + ($percentiles['speedScore'] * .2);
            }
            // 2. No agility so no elusiveness, just broad jump and speedScore
            if ($metrics['elusiveness'] == null && $metrics['power'] !== null && $metrics['speedScore'] !== null) {
                $data['grinder'] = ($percentiles['power'] * .6) + ($percentiles['speedScore'] * .4);
            }

            // 3. Just speedScore
            if ($metrics['elusiveness'] == null && $metrics['power'] == null && $metrics['speedScore'] !== null) {
                $data['grinder'] = $percentiles['speedScore'];
            }

            // 4. Just broadJump
            if ($metrics['elusiveness'] == null && $metrics['power'] !== null && $metrics['speedScore'] == null) {
                $data['grinder'] = $percentiles['power'];
            }

            /*** Make Receiver Base ***/
            // 1. We have agility scores and forty time
            if ($metrics['routeAgility'] !== null && $metrics['jukeAgility'] !== null && $metrics['fortyTime'] !== null) {
                $data['receiver'] = ($percentiles['routeAgility'] * .6) + ($percentiles['jukeAgility'] * .2) + ($percentiles['fortyTime'] * .2);
            }
            // 2. No agility scores just forty time
            if ($metrics['routeAgility'] == null && $metrics['jukeAgility'] == null && $metrics['fortyTime'] !== null) {
                $data['receiver'] = $percentiles['fortyTime'];
            }

            // 3. Agility but no 40 time
            if ($metrics['routeAgility'] == null && $metrics['jukeAgility'] == null && $metrics['fortyTime'] !== null) {
                $data['receiver'] = ($percentiles['routeAgility'] * .7) + ($percentiles['jukeAgility'] * .3);
            }


            if (!empty($rb->getCollegeStats())) {
                $collegeStuff = $this->makeCollegeScore($rb);
                $metrics['collegeScore'] = $collegeStuff['collegeScore'];
                $metrics['breakoutSeasons'] = $collegeStuff['breakoutSeasons'];
                $metrics['bestDominator'] = $collegeStuff['bestDominator'];
                $metrics['bestRecDominator'] = $collegeStuff['bestRecDominator'];
                $metrics['collegeSeasons'] = $collegeStuff['collegeSeasons'];
                $metrics['breakoutClass'] = $collegeStuff['breakoutClass'];
                $metrics['bestYPC'] = $collegeStuff['bestYPC'];
                $metrics['bestCarryDominator'] = $collegeStuff['bestCarryDominator'];

                /*** Use College Stats to adjust scores ***/

                $data['receiver'] = ($percentiles['bestRecDominator'] * .6) + ($data['receiver'] * .4);

                if ($collegeStuff['bestYPC'] < 5) {
                    $data['grinder'] = $data['grinder'] - 10;
                }

                if ($collegeStuff['bestCarryDominator'] < 30) {
                    $data['grinder'] = $data['grinder'] - 10;
                }

            } else {
                $metrics['collegeScore'] = null;
                $metrics['breakoutSeasons'] = "N/A";
                $metrics['bestDominator'] = "N/A";
                $metrics['bestRecDominator'] = "N/A";
                $metrics['collegeSeasons'] = "N/A";
                $metrics['bestYPC'] = "N/A";
                $metrics['bestCarryDominator'] = "N/A";
            }

            $data['alpha'] = ($data['receiver'] * .6) + ($data['grinder'] * .4);

            $metrics['alpha'] = round($data['alpha'],2);
            $metrics['passCatcher'] = round($data['receiver'],2);
            $metrics['grinder'] = round($data['grinder'],2);

            $rb->setMetrics($metrics);
            $this->command->save($rb);

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function calculateSpecialPercentiles($type)
    {
        $percentileArrays = $this->repository->getPercentileRanks($type, $this->specialMetrics);
        $players = $this->repository->findAllPlayers($type);
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($players));
        $pointer = 0;
        foreach ($players as $player) {
            $id = $player->getId();
            $player->decodeJson();
            $percentiles = $player->getPercentiles();
            foreach ($percentileArrays as $name => $value) {
                $percentiles[$name] = (array_key_exists($id, $percentileArrays[$name])) ? round($percentileArrays[$name][$id] * 100, 2) : "";
            }

            $player->setPercentiles($percentiles);

            $this->command->save($player);

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Percentiles completed\n";
    }

    public function makeCollegeScore($rb)
    {
        if ($rb->getId() == 5238) {
            $gotHim = true;
        } else {
            $notHIm = true;
        }

        $i = 0;
        $breakout = false;
        $collegeScore = 0;
        $bestDominator = .01;
        $bestSeason = [];
        $lastYear = "";
        $breakoutClass = "None";
        $bestRecDominator = 0;
        $breakoutSeasons = 0;
        $lastBreakout = 0;
        $bestYPC = 0;
        $bestCarryDom = 0;

        $collegeStats = $rb->getCollegeStats();
        foreach ($collegeStats as $stats) {
            if ($stats->year != "Career") {
                // determine dominators
                $dominator['td'] = $stats['tdDominator'];
                $dominator['yd'] = $stats['ydsDominator'];
                $dominator['rec'] = $stats['recDominator'];
                $dominator['carries'] = round(($stats['rushAtt'] / $stats['totals']['carries']) * 100, 2);
                $breakout = 0;

                foreach ($dominator as $type => $score) {
                    if ($type == 'td') {
                        if ($score !== 0) {
                            switch (true) {
                                case ($score > 10):
                                    $breakout = $breakout + 1;
                                    break;
                                case ($score > 7):
                                    $breakout = $breakout + .5;
                                    break;
                                default:
                            }
                        }
                    }

                    if ($type == 'yd') {
                        if ($score !== 0) {
                            switch (true) {
                                case ($score > 20):
                                    $breakout = $breakout + 1;
                                    break;
                                case ($score > 15):
                                    $breakout = $breakout + .5;
                                    break;
                                default:
                            }
                        }
                    }
                }

                switch ($breakout) {
                    case 2:
                        $breakoutSeasons = $breakoutSeasons + 1;
                        break;
                    case ($breakout >= 1):
                        $breakoutSeasons = $breakoutSeasons + .5;
                        break;
                    default:
                }

                // determine breakout class
                if ($breakout == 2) {
                    if ($breakoutClass == "None") {
                        if ($i == 0) {
                            if ($stats['class'] == "FR") {
                                $breakoutClass = "True Freshman";
                            } else {
                                $breakoutClass = $stats['class'];
                            }
                        } elseif ($i == 1) {
                            if ($stats['class'] == "FR") {
                                $breakoutClass = "Redshirt Freshman";
                            } elseif ($stats['class'] == "SO") {
                                $breakoutClass = "Sophomore";
                            } else {
                                $breakoutClass = $stats['class'];
                            }
                        } elseif ($i == 2) {
                            $breakoutClass = "JR";
                        } else {
                            $breakoutClass = "SR";
                        }
                    }
                }

                // determine best dominators
                $currentDominator = round((array_sum([$dominator['yd'], $dominator['td']])) / 2, 2);
                if ($currentDominator > $bestDominator) {
                    $bestDominator = $currentDominator;
                }

                if ($dominator['rec'] > $bestRecDominator) {
                    $bestRecDominator = $dominator['rec'];
                }

                if ($stats['rushAvg'] > $bestYPC) {
                    $bestYPC = $stats['rushAvg'];
                }

                if ($dominator['carries'] > $bestCarryDom) {
                    $bestCarryDom = $dominator['carries'];
                }
            }
            $lastBreakout = $breakout;
            $conf = $stats['conf'];
            if ($stats['college'] == "Notre Dame") {
                $conf = "ACC";
            }
            $lastYear = $stats['class'];
            $i++;
        }

        $collegeScore = $breakoutSeasons;
        /**** Bonuses ****/
        // Coming out as a junior, add last breakout to simulate senior season
        if ($lastYear !== "SR" && $i < 3) {
            $collegeScore = $collegeScore + $lastBreakout;
        }

        // Breakout class
        if ($breakoutClass == "True Freshman") {
            $collegeScore = $collegeScore + 3;
        } elseif ($breakoutClass == "Somphomore" ||$breakoutClass == "Redshirt Freshman" ) {
            $collegeScore = $collegeScore + 2;
        } elseif ($breakoutClass == "Junior") {
            $collegeScore = $collegeScore + 1;
        } else {
            $collegeScore = $collegeScore + 0;
        }

        //Conference bonus/penalty for not Division 1
        $power5 = ["ACC", "Big Ten", "SEC", "Big 12", "Pac-12"];
        $minor5 = ["MWC", "American", "CUSA", "MAC", "Sun Belt"];
        if (in_array($conf, $power5)) {
            $collegeScore = $collegeScore + 1;
        } elseif (in_array($conf, $minor5)) {
            $collegeScore = $collegeScore + 0;
        } else {
            $collegeScore = $collegeScore - 2;
        }

        // Best breakout score
        switch ($bestDominator) {
            case $bestDominator >= 30:
                $collegeScore = $collegeScore + 3;
                break;
            case $bestDominator >= 20:
                $collegeScore = $collegeScore + 2;
                break;
            case $bestDominator >= 15:
                $collegeScore = $collegeScore + 1;
                break;
            default:
        }

        // Best Recbreakout score
        switch ($bestRecDominator) {
            case $bestDominator >= 15:
                $collegeScore = $collegeScore + 3;
                break;
            case $bestDominator >= 12:
                $collegeScore = $collegeScore + 2;
                break;
            case $bestDominator >= 9:
                $collegeScore = $collegeScore + 1;
                break;
            default:
        }

        if ($breakoutClass == "JR") {
            $breakoutClass = "Junior";
        }

        if ($breakoutClass == "SR") {
            $breakoutClass = "Senior";
        }

        return [
            'collegeScore' => $collegeScore,
            'bestSeason' => $bestSeason,
            'bestRecDominator' => $bestRecDominator,
            'breakoutClass' => $breakoutClass,
            'breakoutSeasons' => $breakoutSeasons,
            "collegeSeasons" => $i,
            "bestDominator" => $bestDominator,
            "bestYPC" => $bestYPC,
            "bestCarryDominator" => $bestCarryDom
        ];
    }

    public function scrapCollegeStats($rb)
    {
        $info = json_decode($rb['player_info']);
        $api = json_decode($rb['api_info']);
        $request = new Request();
        if (1) {
            $cleanFirst = preg_replace('/[^A-Za-z0-9\-]/', '', $rb['first_name']);
            $cleanLast = preg_replace('/[^A-Za-z0-9\-]/', '', $rb['last_name']);
            $cfb = strtolower("{$cleanFirst}-{$cleanLast}")."-3";
        } else {
            $cfb = $api->cfbAlias;
        }
        $request->setUri("https://www.sports-reference.com/cfb/players/{$cfb}.html");

        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();

        $dom = new Query($html);
        $results = $dom->execute('#rushing tr');

        $count = count($results);
        if ($count == 0) {
            return false;
        }
        $collegeStats = [];
        foreach ($results as $result) {
            $rowChildren = $result->childNodes;
            $firstItem = $rowChildren->item(1)->nodeValue;

            if (!empty($firstItem) && $firstItem != 'Year') {
//                if ($rowChildren->item(1)->nodeValue != $info->college) {
//                    return false;
//                }
                $year = $rowChildren->item(0)->nodeValue;
                $year = str_replace("*", "", $year);
                if (! $rowChildren->item(1)->firstChild instanceof \DOMElement) {
                    return false;
                }
                $collegeHref = $rowChildren->item(1)->firstChild->getAttribute("href");
                $totals = $this->getCollegeTotals($collegeHref);
                $collegeStats[$year]['totals'] = $totals;
                $collegeStats[$year]['year'] = $year;
                $collegeStats[$year]['college'] = $rowChildren->item(1)->nodeValue;
                $collegeStats[$year]['conf'] = $rowChildren->item(2)->nodeValue;
                $collegeStats[$year]['class'] = $rowChildren->item(3)->nodeValue;
                $collegeStats[$year]['position'] = $rowChildren->item(4)->nodeValue;
                $collegeStats[$year]['games'] = $rowChildren->item(5)->nodeValue;
                $collegeStats[$year]['rushAtt'] = $rowChildren->item(6)->nodeValue;
                $collegeStats[$year]['rushYds'] = $rowChildren->item(7)->nodeValue;
                $collegeStats[$year]['rushAvg'] = $rowChildren->item(8)->nodeValue;
                $collegeStats[$year]['rushTds'] = $rowChildren->item(9)->nodeValue;
                $collegeStats[$year]['recs'] = $rowChildren->item(10)->nodeValue;
                $collegeStats[$year]['recYds'] = $rowChildren->item(11)->nodeValue;
                $collegeStats[$year]['recAvg'] = $rowChildren->item(12)->nodeValue;
                $collegeStats[$year]['recTds'] = $rowChildren->item(13)->nodeValue;
                $collegeStats[$year]['scrimmageYds'] = $rowChildren->item(15)->nodeValue;
                $collegeStats[$year]['scrimmageTds'] = $rowChildren->item(16)->nodeValue;
                $collegeStats[$year]['ydsDominator'] = (round($collegeStats[$year]['scrimmageYds'] / $totals['yds'], 4)) * 100;
                $collegeStats[$year]['recDominator'] = (round($collegeStats[$year]['recs'] / $totals['recs'], 4)) * 100;
                $collegeStats[$year]['tdDominator'] = (round($collegeStats[$year]['scrimmageTds'] / $totals['tds'], 4)) * 100;
            }
            // $result is a DOMElement
        }

        unset($collegeStats["Career"]);
        $collegeJson = json_encode($collegeStats);

        try {
            $update = <<<EOT
UPDATE player_test SET college_stats = '{$collegeJson}', api_info = JSON_SET(api_info, '$.cfbAlias', '{$cfb}') where id = {$rb['id']};
EOT;
            $stmt   = $this->db->query($update);
            $playerUpdated = $stmt->execute();
        } catch (\Exception $exception) {
            $message = $exception->getMessage();
            return false;
        }
        return true;
    }

    public function getCollegeTotals($href)
    {
        $request = new Request();
        $uri = "https://www.sports-reference.com{$href}";
        $request->setUri($uri);

        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();

        $dom = new Query($html);
        $results = $dom->execute('#team tr');
        $count = count($results);
        $totals = [];
        foreach ($results as $result) {
            $rowChildren = $result->childNodes;
            $firstItem = $rowChildren->item(0)->nodeValue;
            if ($firstItem == "Offense") {
                $games = $rowChildren->item(1)->nodeValue;
                $rushing = $rowChildren->item(8)->nodeValue;
                $scrimmage = $rowChildren->item(12)->nodeValue;
                $totals['rushing'] = $games * $rushing;
                $totals['scrimmage'] = $games * $scrimmage;
            }
        }

        $weird = strpos($html, '<div class="overthrow table_container" id="div_rushing_and_receiving">');
        // $pos = ‌‌strpos($html, '<div class="overthrow table_container" id="div_rushing_and_receiving">');
        $newhtml =  substr($html, $weird);

        $dom = new Query($newhtml);
        $results = $dom->execute('#rushing_and_receiving tr');
        $count = count($results);
        $totals['recs'] = 0;
        $totals['yds'] = 0;
        $totals['tds'] = 0;
        $totals['carries'] = 0;
        foreach ($results as $result) {
            $rowChildren = $result->childNodes;
            $firstItem = $rowChildren->item(0)->nodeValue;
            if ($firstItem > 0.5) {
                $carries = $rowChildren->item(2)->nodeValue;
                $totals['carries'] = $carries + $totals['carries'];
                $recs = $rowChildren->item(6)->nodeValue;
                $totals['recs'] = $recs + $totals['recs'];
                $yds = $rowChildren->item(11)->nodeValue;
                $totals['yds'] = $yds + $totals['yds'];
                $tds = $rowChildren->item(13)->nodeValue;
                $totals['tds'] = $tds + $totals['tds'];
            }
        }

        return $totals;
    }
}