<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/5/19
 * Time: 1:00 PM
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

class WrService extends ServiceAbstract
{

    private $consoleAdapter;
    private $repository;
    private $command;
    private $db;

    public $specialMetrics = [
        'slot' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'deep' => [
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

    public function calculateSpecialScores($type)
    {
        $wrs = $this->repository->findAllPlayers("WR");
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $wrs->count());
        $pointer = 0;
        foreach ($wrs as $wr) {
            $wr->decodeJson();
            $info = $wr->getPlayerInfo();
            $metrics = $wr->getMetrics();
            $percentiles = $wr->getPercentiles();

            if (empty($metrics)) {
                continue;
            }

            //slot score
            $slot = 0;
            $slot = round(($percentiles['routeAgility'] * .7) + ($percentiles['elusiveness'] * .3),2);
            $deep = round(($percentiles['fortyTime'] * .7) + ($percentiles['jumpball'] * .3), 2);

            $metrics["deep"] = $deep;
            $metrics["slot"] = $slot;

            if ($wr->college_stats != null) {
                $college = $this->makeCollegeScore($wr);
                $metrics['collegeScore'] = $college['collegeScore'];
                $metrics['bestSeason'] = $college['bestSeason'];
                $metrics['breakoutClass'] = $college['breakoutClass'];
                $metrics['breakoutSeasons'] = $college['breakoutSeasons'];
                $metrics['collegeSeasons'] = $college['collegeSeasons'];
                $metrics['bestDominator'] = $college['bestDominator'];
            } else {
                $metrics['collegeScore'] = null;
                $metrics['bestSeason'] = null;
                $metrics['breakoutClass'] = null;
            }


            // Alpha Score
            /*
             * 40% college base
             * 20% getting off the line (60% bully, 30% speed, 10% juke)
             * 20% seperation (60% route Agility, 40% speed)
             * 20% Jumpball
             */

            $alphaScore = round(((($metrics['collegeScore']/12) * 100) * .5) + ($deep * .25) + ($slot * .25), 2);

//            // Penalties
            // not getting off the press
            if ($metrics['bully'] < 14) {
                $alphaScore = $alphaScore - 3;
            }

            if ($metrics['bully'] < 9) {
                $alphaScore = $alphaScore - 3;
            }

            if ($metrics['bully'] > 18) {
                $alphaScore = $alphaScore + 2;
            }

            if ($metrics['bully'] > 23 ) {
                $alphaScore = $alphaScore + 2;
            }
//
//            // not commanding cushion or running past cb
            if ($metrics['fortyTime'] > 4.56) {
                $alphaScore = $alphaScore - 3;
            }

            if ($metrics['fortyTime'] > 4.65) {
                $alphaScore = $alphaScore - 3;
            }
//
//            if ($metrics['fortyTime'] < 4.46) {
//                $alphaScore = $alphaScore + 1;
//            }
//
//            if ($metrics['fortyTime'] < 4.40) {
//                $alphaScore = $alphaScore + 1;
//            }
//
//            //not creating separation
            if ($metrics['routeAgility'] > 7.84) {
                $alphaScore = $alphaScore - 3;
            }

            if ($metrics['routeAgility'] > 8.0) {
                $alphaScore = $alphaScore - 4;
            }
//
//            if ($metrics['routeAgility'] < 7.7) {
//                $alphaScore = $alphaScore + 1;
//            }
//
//            if ($metrics['routeAgility'] < 7.5) {
//                $alphaScore = $alphaScore + 1;
//            }
//
//            // not winning contested catches
            if ($metrics['jumpball'] < 140) {
                $alphaScore = $alphaScore - 4;
            }

            if ($metrics['jumpball'] < 135) {
                $alphaScore = $alphaScore - 3;
            }
//
//            if ($metrics['jumpball'] < 143) {
//                $alphaScore = $alphaScore + 1;
//            }
//
//            if ($metrics['jumpball'] < 146) {
//                $alphaScore = $alphaScore + 1;
//            }

            $metrics['alpha'] = round($alphaScore,2);

            $wr->setMetrics($metrics);

            $this->command->save($wr);

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Special Metrics completed\n";
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



    public function makeCollegeScore($wr)
    {
        $collegeStats = $wr->college_stats;
        if ($wr->getId() == 3805) {
            $gotHim = true;
        }
        $i = 0;
        $breakout = false;
        $collegeScore = 0;
        $bestDominator = .01;
        $bestSeason = [];
        $lastYear = "";
        $breakoutClass = "None";
        $bestReturn = 0;
        $breakoutSeasons = 0;
        $lastBreakout = 0;
        $i = 0;
        foreach ($collegeStats as $stats) {
            if ($stats->year != "Career") {

                // determine dominators
                $dominator['td'] = round(($stats['recTds'] / $stats['totals']['tds'] ) * 100, 2);
                $dominator['yd'] = round(($stats['recYds'] / $stats['totals']['yds']) * 100, 2);
                $dominator['rec'] = round(($stats['recs'] / $stats['totals']['recs']) * 100, 2);
                $breakout = 0;

                if ($dominator['rec'] > 20 && (($dominator['yd'] + $dominator['td'])/2) > 20) {
                    $breakout = 3;
                } else {
                    // get breakout season score
                    foreach($dominator as $type => $score) {
                        if ($score !== 0) {
                            switch (true) {
                                case ($score > (20)):
                                    $breakout = $breakout + 1;
                                    break;
                                case ($score > (15)):
                                    $breakout = $breakout + .5;
                                    break;
                                case ($score > (10)):
                                    $breakout = $breakout + .25;
                                case $breakout;
                                default:
                            }
                        }
                    }
                }

                // add to breakout seasons
                switch ($breakout) {
                    case 3:
                        $breakoutSeasons = $breakoutSeasons + 1;
                        break;
                    case ($breakout >= 2):
                        $breakoutSeasons = $breakoutSeasons + .5;
                        break;
                    case ($breakout >= 1):
                        $breakoutSeasons = $breakoutSeasons + .25;
                        break;
                    default:
                }

                // determine breakout class
                if ($breakout == 3 ) {
                    if ($breakoutClass == "None") {
                        if ($i == 0) {
                            if ($stats['class'] == "FR") {
                                $breakoutClass = "True Freshman";
                            } else {
                                $breakoutClass = $stats['class'];
                            }
                        } elseif ($i == 1 ) {
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

                // determine best dominator
                $currentDominator = round((array_sum([$dominator['yd'], $dominator['td']])) / 2, 2);
                if ($currentDominator > $bestDominator) {
                    $bestDominator = $currentDominator;
                    $bestSeason = $stats;
                    $bestSeason['ydsDominator'] = $dominator['yd'];
                    $bestSeason['tdsDominator'] = $dominator['td'];
                }

                // determine return dominator
//                if ($stats['returnStats']['puntYds'] > 0) {
//                    $bestReturn = $stats['returnStats']['puntYds'] + $stats['returnStats']['kickYds'];
//                }

                // save last year
                $lastBreakout = $breakout;
                $conf = $stats['conference'];
                if ($stats['college'] == "Notre Dame") {
                    $conf = "ACC";
                }
                $lastYear = $stats['class'];
                $i++;
            }
        }
        $collegeScore = round(($breakoutSeasons/$i) * 10, 2);
        /**** Bonuses ****/
        // Coming out as a junior, add last breakout to simulate senior season
        if ($lastYear !== "SR" && $i < 3) {
            $collegeScore = $collegeScore + $lastBreakout;
        }

        // Breakout class
        if ($breakoutClass == "FR") {
            $collegeScore = $collegeScore + 3;
        } elseif ($breakoutClass == "SO") {
            $collegeScore = $collegeScore + 2;
        } elseif ($breakoutClass == "JR") {
            $collegeScore = $collegeScore + 1;
        } else {
            $collegeScore = $collegeScore + 0;
        }

        //Conference bonus/penalty for not Division 1
        $power5 = ["ACC", "Big Ten", "SEC", "Big 12", "Pac-12"];
        $minor5 = ["MWC", "American", "CUSA", "MAC", "Sun Belt"];
        if (in_array($conf, $power5) ) {
            $collegeScore = $collegeScore + 1;
        } elseif (in_array($conf, $minor5)) {
            $collegeScore = $collegeScore + 0;
        } else {
            $collegeScore = $collegeScore - 2;
        }

        // Best breakout score
        switch ($bestDominator) {
            case $bestDominator >= 40:
                $collegeScore = $collegeScore + 2;
                break;
            case $bestDominator >= 35:
                $collegeScore = $collegeScore + 1.5;
                break;
            case $bestDominator >= 30:
                $collegeScore = $collegeScore + 1;
                break;
            default:
        }

//        if ($bestReturn != 0) {
//            switch ($bestReturn) {
//                case $bestReturn > 1000:
//                    $collegeScore = $collegeScore + 4;
//                    break;
//                case $bestReturn > 750:
//                    $collegeScore = $collegeScore + 3;
//                    break;
//                case $bestReturn > 500:
//                    $collegeScore = $collegeScore + 2;
//                    break;
//                case $bestReturn > 250:
//                    $collegeScore = $collegeScore + 1;
//                    break;
//                default:
//            }
//        }

        if ($breakoutClass == "JR") {
            $breakoutClass = "Junior";
        }

        if ($breakoutClass == "SR") {
            $breakoutClass = "Senior";
        }

        return [
            'collegeScore' => $collegeScore,
            'bestSeason' => $bestSeason,
            'bestReturn' => $bestReturn,
            'breakoutClass' => $breakoutClass,
            'breakoutSeasons' => $breakoutSeasons,
            "collegeSeasons" => $i,
            "bestDominator" => $bestDominator
        ];

    }


    public function scrapCollegeJob()
    {
        $sql =<<<EOT
Select * from player_test
where position = 'WR'
      and player_info->'$.active'
      and college_stats is null
      and team is not null
      and json_unquote(player_info->'$.college') not in ('-', 'None')
EOT;

        $stmt   = $this->db->query($sql);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $count = $resultSet->count();
        $wrs = $resultSet->toArray();

        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;

        foreach ($wrs as $wr) {
            $result = $this->scrapCollegeStats($wr);
            if ($result == false) {
                continue;
            }
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function scrapCollegeStats($wr)
    {
        $info = json_decode($wr['player_info']);
        $api = json_decode($wr['api_info']);
        $request = new Request();
        if (empty($api->cfbAlias)) {
            return false;
//            $cleanFirst = preg_replace('/[^A-Za-z0-9\-]/', '', $wr['first_name']);
//            $cleanLast = preg_replace('/[^A-Za-z0-9\-]/', '', $wr['last_name']);
//            $cfb = strtolower("{$cleanFirst}-{$cleanLast}")."-3";
        } else {
            $cfb = $api->cfbAlias;
        }
        $request->setUri("https://www.sports-reference.com/cfb/players/{$cfb}.html");

        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();

        $dom = new Query($html);
        $results = $dom->execute('#receiving tr');

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
                $collegeStats[$year]['conference'] = $rowChildren->item(2)->nodeValue;
                $collegeStats[$year]['class'] = $rowChildren->item(3)->nodeValue;
                $collegeStats[$year]['position'] = $rowChildren->item(4)->nodeValue;
                $collegeStats[$year]['games'] = $rowChildren->item(5)->nodeValue;
                $collegeStats[$year]['recs'] = $rowChildren->item(6)->nodeValue;
                $collegeStats[$year]['recYds'] = $rowChildren->item(7)->nodeValue;
                $collegeStats[$year]['recAvg'] = $rowChildren->item(8)->nodeValue;
                $collegeStats[$year]['recTds'] = $rowChildren->item(9)->nodeValue;
                $collegeStats[$year]['rushes'] = $rowChildren->item(10)->nodeValue;
                $collegeStats[$year]['rushYds'] = $rowChildren->item(11)->nodeValue;
                $collegeStats[$year]['rushAvg'] = $rowChildren->item(12)->nodeValue;
                $collegeStats[$year]['rushTds'] = $rowChildren->item(13)->nodeValue;
            }
            // $result is a DOMElement
        }


        $returns = strpos($html, '<div class="overthrow table_container" id="div_punt_ret">');
        $returnHtml =  substr($html, $returns);

        $dom = new Query($returnHtml);
        $results = $dom->execute('#punt_ret tr');
        $count = count($results);
        $returnStats = [];
        foreach ($results as $k => $result) {
            $rowChildren = $result->childNodes;
            $year = $rowChildren->item(0)->nodeValue;
            $year = str_replace("*", "", $year);
            if ($year > 0.5) {
                if (!array_key_exists('returnYds', $collegeStats[$year]['totals'])){
                    return false;
                }
                $returnStats['puntYds'] = $rowChildren->item(7)->nodeValue;
                $returnStats['puntAvg'] = $rowChildren->item(8)->nodeValue;
                $returnStats['puntTds'] = $rowChildren->item(9)->nodeValue;
                $returnStats['kickYds'] = $rowChildren->item(11)->nodeValue;
                $returnStats['kickAvg'] = $rowChildren->item(12)->nodeValue;
                $returnStats['kickTds'] = $rowChildren->item(13)->nodeValue;
                $collegeStats[$year]['returnStats'] = $returnStats;
            }
        }

        unset($collegeStats["Career"]);
        $collegeJson = json_encode($collegeStats);

        try {
            $update = <<<EOT
UPDATE player_test SET college_stats = '{$collegeJson}', api_info = JSON_SET(api_info, '$.cfbAlias', '{$cfb}') where id = {$wr['id']};
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

        $weird = strpos($html, '<div class="overthrow table_container" id="div_rushing_and_receiving">');
        //$pos = ‌‌strpos($html, '<div class="overthrow table_container" id="div_rushing_and_receiving">');
        $newhtml =  substr($html, $weird);

        $dom = new Query($newhtml);
        $results = $dom->execute('#rushing_and_receiving tr');
        $count = count($results);
        $total['recs'] = 0;
        $total['yds'] = 0;
        $total['tds'] = 0;
        foreach ($results as $result) {
            $rowChildren = $result->childNodes;
            $firstItem = $rowChildren->item(0)->nodeValue;
            if ($firstItem > 0.5) {
                $recs = $rowChildren->item(6)->nodeValue;
                $total['recs'] = $recs + $total['recs'];
                $yds = $rowChildren->item(7)->nodeValue;
                $total['yds'] = $yds + $total['yds'];
                $tds = $rowChildren->item(9)->nodeValue;
                $total['tds'] = $tds + $total['tds'];
            }
        }

        $returns = strpos($html, '<div class="overthrow table_container" id="div_returns">');
        $returnHtml =  substr($html, $returns);

        $domReturn = new Query($returnHtml);
        $results = $domReturn->execute('#returns tr');
        $count = count($results);
        $total['returnYds'] = 0;
        $total['returnTds'] = 0;
        foreach ($results as $result) {
            $rowChildren = $result->childNodes;
            $firstItem = $rowChildren->item(0)->nodeValue;
            if ($firstItem > 0.5) {
                $puntYds = $rowChildren->item(3)->nodeValue;
                $puntTds = $rowChildren->item(5)->nodeValue;
                $kickYds = $rowChildren->item(7)->nodeValue;
                $kickTds = $rowChildren->item(9)->nodeValue;
                $total['returnYds'] = $total['returnYds'] + $puntYds + $kickYds;
                $total['returnTds'] =  $total['returnTds'] + $puntTds + $kickTds;
            }
        }
        return $total;
    }
}

//class SqlWrCommand
//{
//
//    /**
//     * @param string $type
//     * @return mixed
//     */
//
//
//    public function calculateSpecialPercentiles()
//    {
//        $sql = <<<EOT
//SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.jukeAgility'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.jukeAgility'),4,'0')) percentile_rank
//FROM player_test
//WHERE metrics->'$.jukeAgility' != '0' AND position = 'WR'
//EOT;
//        $stmt= $this->db->query($sql);
//        $result = $stmt->execute();
//        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
//            return [];
//        }
//
//        $resultSet = new ResultSet();
//        $resultSet->initialize($result);
//        $jukeAgility = [];
//        foreach($resultSet as $row) {
//            $jukeAgility[$row->id] = $row->percentile_rank;
//        }
//        print "juke index built\n";
//
//        /**************************************************************************/
//        $sql = <<<EOT
//SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.routeAgility'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.routeAgility'),4,'0')) percentile_rank
//FROM player_test
//WHERE metrics->'$.routeAgility' != '0' AND position = 'WR'
//EOT;
//        $stmt= $this->db->query($sql);
//        $result = $stmt->execute();
//        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
//            return [];
//        }
//
//        $resultSet = new ResultSet();
//        $resultSet->initialize($result);
//        $routeAgility = [];
//        foreach($resultSet as $row) {
//            $routeAgility[$row->id] = $row->percentile_rank;
//        }
//        print "route index built\n";
//        /**************************************************************************/
//        $sql = <<<EOT
//SELECT id, metrics->'$.alpha', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.alpha'),3),6,'0') ASC),3) percentile_rank
//FROM player_test
//WHERE metrics->'$.alpha' IS NOT NULL AND metrics->'$.alpha' != '-'AND position = 'WR'
//EOT;
//        $stmt= $this->db->query($sql);
//        $result = $stmt->execute();
//        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
//            return [];
//        }
//
//        $resultSet = new ResultSet();
//        $resultSet->initialize($result);
//        $alpha = [];
//        foreach($resultSet as $row) {
//            $alpha[$row->id] = $row->percentile_rank;
//        }
//        print "alpha index built\n";
//        /**************************************************************************/
//        $sql = <<<EOT
//SELECT id, metrics->'$.slot', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.slot'),3),6,'0') ASC),3) percentile_rank
//FROM player_test
//WHERE metrics->'$.slot' IS NOT NULL AND metrics->'$.slot' != '-'AND position = 'WR'
//EOT;
//        $stmt= $this->db->query($sql);
//        $result = $stmt->execute();
//        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
//            return [];
//        }
//
//        $resultSet = new ResultSet();
//        $resultSet->initialize($result);
//        $slot = [];
//        foreach($resultSet as $row) {
//            $slot[$row->id] = $row->percentile_rank;
//        }
//        print "slot index built\n";
//        /**************************************************************************/
//        $sql = <<<EOT
//SELECT id, metrics->'$.deep', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.deep'),3), 6, '0') ASC),3) percentile_rank
//FROM player_test
//WHERE metrics->'$.deep' IS NOT NULL AND metrics->'$.deep' != '-'AND position = 'WR'
//EOT;
//        $stmt= $this->db->query($sql);
//        $result = $stmt->execute();
//        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
//            return [];
//        }
//
//        $resultSet = new ResultSet();
//        $resultSet->initialize($result);
//        $deep = [];
//        foreach($resultSet as $row) {
//            $deep[$row->id] = $row->percentile_rank;
//        }
//        print "deep index built\n";
//        /**************************************************************************/
//        $sql = <<<EOT
//SELECT id, metrics->'$.collegeScore', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.collegeScore'),3),6,'0') ASC),3) percentile_rank
//FROM player_test
//WHERE metrics->'$.collegeScore' IS NOT NULL AND metrics->'$.collegeScore' != '-'AND position = 'WR'
//EOT;
//        $stmt= $this->db->query($sql);
//        $result = $stmt->execute();
//        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
//            return [];
//        }
//
//        $resultSet = new ResultSet();
//        $resultSet->initialize($result);
//        $college = [];
//        foreach($resultSet as $row) {
//            $college[$row->id] = $row->percentile_rank;
//        }
//        print "collegeScore index built\n";
//        /**************************************************************************/
//        $sql = <<<EOT
//SELECT id, metrics->'$.yacScore', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.yacScore'),3),6,'0') ASC),3) percentile_rank
//FROM player_test
//WHERE metrics->'$.yacScore' IS NOT NULL AND metrics->'$.yacScore' != '-'AND position = 'WR'
//EOT;
//        $stmt= $this->db->query($sql);
//        $result = $stmt->execute();
//        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
//            return [];
//        }
//
//        $resultSet = new ResultSet();
//        $resultSet->initialize($result);
//        $yac = [];
//        foreach($resultSet as $row) {
//            $yac[$row->id] = $row->percentile_rank;
//        }
//        print "alpha index built\n";
//
//        $sql    = new Sql($this->db);
//        $select = $sql->select();
//        $select->from(['p' => 'player_test']);
//        $select->where(['p.position = ?' => 'WR']);
//        $stmt   = $sql->prepareStatementForSqlObject($select);
//        $result = $stmt->execute();
//
//        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
//            return [];
//        }
//
//        $resultSet = new ResultSet();
//        $resultSet->initialize($result);
//        $count = $resultSet->count();
//        $wrs = $resultSet->toArray();
//        print "Special Percentages starting\n";
//        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
//        $pointer = 0;
//        foreach ($wrs as $wr) {
//            $id = $wr['id'];
//            $data['alpha'] = (array_key_exists($id, $alpha)) ? $alpha[$id] * 100 : "";
//            $data['slot'] = (array_key_exists($id, $slot)) ? $slot[$id] * 100 : "";
//            $data['deep'] = (array_key_exists($id, $deep)) ? $deep[$id] * 100 : "";
//            $data['collegeScore'] = (array_key_exists($id, $college)) ? $college[$id] * 100 : "";
//            $data['yacScore'] = (array_key_exists($id, $yac)) ? $yac[$id] * 100 : "";
//            $data['jukeAgility'] = (array_key_exists($id, $jukeAgility)) ? $jukeAgility[$id] * 100 : "";
//            $data['routeAgility'] = (array_key_exists($id, $routeAgility)) ? $routeAgility[$id] * 100 : "";
//
//            $jsonString = "";
//            foreach ($data as $key => $value) {
//                $jsonString .= ", '$.{$key}', '{$value}'";
//            }
//
//            try {
//                $update = <<<EOT
//UPDATE player_test SET percentiles = json_set(percentiles{$jsonString}) where id = {$id};
//EOT;
//                $stmt   = $this->db->query($update);
//                $playerUpdated = $stmt->execute();
//            } catch (\Exception $exception) {
//                $message = $exception->getMessage();
//            }
//
//            $pointer++;
//            $progressBar->update($pointer);
//        }
//        $progressBar->finish();
//        print "Special Percentages completed\n";
//    }
//
//
//
//
//    public function scrapCollegeJob()
//    {
//        $sql =<<<EOT
//Select * from player_test
//where position = 'WR'
//      and player_info->'$.active'
//      and college_stats is null
//      and team is not null
//      and json_unquote(player_info->'$.college') not in ('-', 'None')
//EOT;
//
//        $stmt   = $this->db->query($sql);
//        $result = $stmt->execute();
//
//        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
//            return [];
//        }
//
//        $resultSet = new ResultSet();
//        $resultSet->initialize($result);
//        $count = $resultSet->count();
//        $wrs = $resultSet->toArray();
//
//        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
//        $pointer = 0;
//
//        foreach ($wrs as $wr) {
//            $result = $this->scrapCollegeStats($wr);
//            if ($result == false) {
//                continue;
//            }
//            $pointer++;
//            $progressBar->update($pointer);
//        }
//        $progressBar->finish();
//    }
//
//    public function scrapCollegeStats($wr)
//    {
//        $info = json_decode($wr['player_info']);
//        $api = json_decode($wr['api_info']);
//        $request = new Request();
//        if (empty($api->cfbAlias)) {
//            return false;
////            $cleanFirst = preg_replace('/[^A-Za-z0-9\-]/', '', $wr['first_name']);
////            $cleanLast = preg_replace('/[^A-Za-z0-9\-]/', '', $wr['last_name']);
////            $cfb = strtolower("{$cleanFirst}-{$cleanLast}")."-3";
//        } else {
//            $cfb = $api->cfbAlias;
//        }
//        $request->setUri("https://www.sports-reference.com/cfb/players/{$cfb}.html");
//
//        $client = new Client();
//        $response = $client->send($request);
//        $html = $response->getBody();
//
//        $dom = new Query($html);
//        $results = $dom->execute('#receiving tr');
//
//        $count = count($results);
//        if ($count == 0) {
//            return false;
//        }
//        $collegeStats = [];
//        foreach ($results as $result) {
//            $rowChildren = $result->childNodes;
//            $firstItem = $rowChildren->item(1)->nodeValue;
//
//            if (!empty($firstItem) && $firstItem != 'Year') {
////                if ($rowChildren->item(1)->nodeValue != $info->college) {
////                    return false;
////                }
//                $year = $rowChildren->item(0)->nodeValue;
//                $year = str_replace("*", "", $year);
//                if (! $rowChildren->item(1)->firstChild instanceof \DOMElement) {
//                    return false;
//                }
//                $collegeHref = $rowChildren->item(1)->firstChild->getAttribute("href");
//                $totals = $this->getCollegeTotals($collegeHref);
//                $collegeStats[$year]['totals'] = $totals;
//                $collegeStats[$year]['year'] = $year;
//                $collegeStats[$year]['college'] = $rowChildren->item(1)->nodeValue;
//                $collegeStats[$year]['conference'] = $rowChildren->item(2)->nodeValue;
//                $collegeStats[$year]['class'] = $rowChildren->item(3)->nodeValue;
//                $collegeStats[$year]['position'] = $rowChildren->item(4)->nodeValue;
//                $collegeStats[$year]['games'] = $rowChildren->item(5)->nodeValue;
//                $collegeStats[$year]['recs'] = $rowChildren->item(6)->nodeValue;
//                $collegeStats[$year]['recYds'] = $rowChildren->item(7)->nodeValue;
//                $collegeStats[$year]['recAvg'] = $rowChildren->item(8)->nodeValue;
//                $collegeStats[$year]['recTds'] = $rowChildren->item(9)->nodeValue;
//                $collegeStats[$year]['rushes'] = $rowChildren->item(10)->nodeValue;
//                $collegeStats[$year]['rushYds'] = $rowChildren->item(11)->nodeValue;
//                $collegeStats[$year]['rushAvg'] = $rowChildren->item(12)->nodeValue;
//                $collegeStats[$year]['rushTds'] = $rowChildren->item(13)->nodeValue;
//            }
//            // $result is a DOMElement
//        }
//
//
//        $returns = strpos($html, '<div class="overthrow table_container" id="div_punt_ret">');
//        $returnHtml =  substr($html, $returns);
//
//        $dom = new Query($returnHtml);
//        $results = $dom->execute('#punt_ret tr');
//        $count = count($results);
//        $returnStats = [];
//        foreach ($results as $k => $result) {
//            $rowChildren = $result->childNodes;
//            $year = $rowChildren->item(0)->nodeValue;
//            $year = str_replace("*", "", $year);
//            if ($year > 0.5) {
//                if (!array_key_exists('returnYds', $collegeStats[$year]['totals'])){
//                    return false;
//                }
//                $returnStats['puntYds'] = $rowChildren->item(7)->nodeValue;
//                $returnStats['puntAvg'] = $rowChildren->item(8)->nodeValue;
//                $returnStats['puntTds'] = $rowChildren->item(9)->nodeValue;
//                $returnStats['kickYds'] = $rowChildren->item(11)->nodeValue;
//                $returnStats['kickAvg'] = $rowChildren->item(12)->nodeValue;
//                $returnStats['kickTds'] = $rowChildren->item(13)->nodeValue;
//                $collegeStats[$year]['returnStats'] = $returnStats;
//            }
//        }
//
//        unset($collegeStats["Career"]);
//        $collegeJson = json_encode($collegeStats);
//
//        try {
//            $update = <<<EOT
//UPDATE player_test SET college_stats = '{$collegeJson}', api_info = JSON_SET(api_info, '$.cfbAlias', '{$cfb}') where id = {$wr['id']};
//EOT;
//            $stmt   = $this->db->query($update);
//            $playerUpdated = $stmt->execute();
//        } catch (\Exception $exception) {
//            $message = $exception->getMessage();
//            return false;
//        }
//        return true;
//    }
//
//    public function getCollegeTotals($href)
//    {
//        $request = new Request();
//        $uri = "https://www.sports-reference.com{$href}";
//        $request->setUri($uri);
//
//        $client = new Client();
//        $response = $client->send($request);
//        $html = $response->getBody();
//
//        $weird = strpos($html, '<div class="overthrow table_container" id="div_rushing_and_receiving">');
//        //$pos = ‌‌strpos($html, '<div class="overthrow table_container" id="div_rushing_and_receiving">');
//        $newhtml =  substr($html, $weird);
//
//        $dom = new Query($newhtml);
//        $results = $dom->execute('#rushing_and_receiving tr');
//        $count = count($results);
//        $total['recs'] = 0;
//        $total['yds'] = 0;
//        $total['tds'] = 0;
//        foreach ($results as $result) {
//            $rowChildren = $result->childNodes;
//            $firstItem = $rowChildren->item(0)->nodeValue;
//            if ($firstItem > 0.5) {
//                $recs = $rowChildren->item(6)->nodeValue;
//                $total['recs'] = $recs + $total['recs'];
//                $yds = $rowChildren->item(7)->nodeValue;
//                $total['yds'] = $yds + $total['yds'];
//                $tds = $rowChildren->item(9)->nodeValue;
//                $total['tds'] = $tds + $total['tds'];
//            }
//        }
//
//        $returns = strpos($html, '<div class="overthrow table_container" id="div_returns">');
//        $returnHtml =  substr($html, $returns);
//
//        $domReturn = new Query($returnHtml);
//        $results = $domReturn->execute('#returns tr');
//        $count = count($results);
//        $total['returnYds'] = 0;
//        $total['returnTds'] = 0;
//        foreach ($results as $result) {
//            $rowChildren = $result->childNodes;
//            $firstItem = $rowChildren->item(0)->nodeValue;
//            if ($firstItem > 0.5) {
//                $puntYds = $rowChildren->item(3)->nodeValue;
//                $puntTds = $rowChildren->item(5)->nodeValue;
//                $kickYds = $rowChildren->item(7)->nodeValue;
//                $kickTds = $rowChildren->item(9)->nodeValue;
//                $total['returnYds'] = $total['returnYds'] + $puntYds + $kickYds;
//                $total['returnTds'] =  $total['returnTds'] + $puntTds + $kickTds;
//            }
//        }
//        return $total;
//    }
//
//    public function makeCollegeScore($player)
//    {
//        //get breakout score
//        $collegeStats = $player->college_stats;
//        $i = 0;
//        $breakout = false;
//        $collegeScore = 0;
//        $bestDominator = .01;
//        $bestSeason = [];
//        $lastYear = "";
//        $breakoutClass = "None";
//        $bestReturn = 0;
//        $breakoutSeasons = 0;
//        $i = 0;
//        foreach ($collegeStats as $stats) {
//            if ($stats->year != "Career") {
//                // determine dominators
//                $dominator['td'] = round($stats->recTds / $stats->totals->tds * 100, 2);
//                $dominator['yd'] = round($stats->recYds / $stats->totals->yds * 100, 2);
//                $dominator['rec'] = round($stats->recYds / $stats->totals->recs * 100, 2);
//                $breakout = 0;
//
//                // get breakout season score
//                foreach($dominator as $type => $score) {
//                    switch ($score) {
//                        case ($score > 20):
//                            $breakout = $breakout + 1;
//                            break;
//                        case ($score > 15):
//                            $breakout = $breakout + .5;
//                            break;
//                        case ($score > 10):
//                            $breakout = $breakout + .25;
//                            case $breakout;
//                        default:
//                    }
//                }
//
//                // add to breakout seasons
//                switch ($breakout) {
//                    case 3:
//                        $breakoutSeasons = $breakoutSeasons + 1;
//                        break;
//                    case ($breakout >= 2):
//                        $breakoutSeasons = $breakoutSeasons + .5;
//                        break;
//                    case ($breakout >= 1):
//                        $breakoutSeasons = $breakoutSeasons + .25;
//                        break;
//                    default:
//                }
//
//                // determine breakout class
//                if ($breakout == 3 ) {
//                    if ($breakoutClass == "") {
//                        if ($i == 0 && $stats->class == "FR") {
//                            $breakoutClass = "FR";
//                        } elseif ($i == 1) {
//                            $breakoutClass = "SO";
//                        } elseif ($i == 2) {
//                            $breakoutClass = "JR";
//                        } else {
//                            $breakoutClass = "SR";
//                        }
//                    }
//                }
//
//                // determine best dominator
//                $currentDominator = (array_sum([$dominator['yd'], $dominator['td']])) / 2;
//                if ($currentDominator > $bestDominator) {
//                    $bestDominator = $currentDominator;
//                    $bestSeason = $stats;
//                    $bestSeason->ydsDominator = $dominator['yd'];
//                    $bestSeason->tdsDominator = $dominator['td'];
//                }
//
//                // determine return dominator
//                if ($stats->returnStats->puntYds > 0) {
//                    $bestReturn = $stats->returnStats->puntYds + $stats->returnStats->kickYds;
//                }
//
//                // save last year
//                $lastYear = $stats->class;
//                $i++;
//            }
//        }
//        $collegeScore = $breakoutSeasons;
//        /**** Bonuses ****/
//        // Coming out as a junior
//        if ($lastYear !== "SR" && $i < 4) {
//            $collegeScore = $collegeScore + 1;
//        }
//
//        // Best breakout score
//        switch ($bestDominator) {
//            case $bestDominator >= 40:
//                $collegeScore = $collegeScore + 3;
//                break;
//            case $bestDominator >= 35:
//                $collegeScore = $collegeScore + 2;
//                break;
//            case $bestDominator >= 30:
//                $collegeScore = $collegeScore + 1;
//                break;
//            default:
//        }
//
////        if ($bestReturn != 0) {
////            switch ($bestReturn) {
////                case $bestReturn > 1000:
////                    $collegeScore = $collegeScore + 4;
////                    break;
////                case $bestReturn > 750:
////                    $collegeScore = $collegeScore + 3;
////                    break;
////                case $bestReturn > 500:
////                    $collegeScore = $collegeScore + 2;
////                    break;
////                case $bestReturn > 250:
////                    $collegeScore = $collegeScore + 1;
////                    break;
////                default:
////            }
////        }
//
//        return [
//            'collegeScore' => $collegeScore,
//            'bestSeason' => $bestSeason,
//            'bestReturn' => $bestReturn,
//            'breakoutClass' => $breakoutClass,
//            'breakoutSeasons' => $breakoutSeasons
//        ];
//
//    }
//}