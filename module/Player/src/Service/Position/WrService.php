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

class WrService extends PlayerAbstract
{

    private $consoleAdapter;
    private $repository;
    private $command;
    private $db;

    public $specialMetrics = [
        'yacScore' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
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
    }

    public function calculateSpecialScores($type)
    {
        $wrs = $this->repository->findAllPlayers("WR");
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $wrs->count());
        $pointer = 0;
        foreach ($wrs as $wr) {
            $info = json_decode($wr['player_info']);
            $metrics = json_decode($wr['metrics']);
            $percentiles = json_decode($wr['percentiles']);

            $yacScore = ($percentiles->power * .35) + ($percentiles->elusiveness *.65);

            //slot score
            $slot = 0;
            switch ($percentiles->agility) {
                case $percentiles->agility > 85:
                    $slot = $slot + 5;
                    break;
                case $percentiles->agility > 75:
                    $slot = $slot + 4;
                    break;
                case $percentiles->agility > 60:
                    $slot = $slot + 3;
                    break;
                case $percentiles->agility > 50:
                    $slot = $slot + 2;
                    break;
                case $percentiles->agility > 40:
                    $slot = $slot + 1;
                    break;
                default:
                    $slot;
            }

            switch ($yacScore) {
                case $yacScore > 85:
                    $slot = $slot + 5;
                    break;
                case $yacScore > 75:
                    $slot = $slot + 4;
                    break;
                case $yacScore > 60:
                    $slot = $slot + 3;
                    break;
                case $yacScore > 50:
                    $slot = $slot + 2;
                    break;
                case $yacScore > 40:
                    $slot = $slot + 1;
                    break;
                default:
                    $slot;
            }

            if ($info->heightInches > 73) {
                $slot = $slot + 1;
            }

            $deep = 0;

            switch (true) {
                case $percentiles->fortyTime > 95:
                    $deep = $deep + 7;
                    break;
                case $percentiles->fortyTime > 90:
                    $deep = $deep + 6;
                    break;
                case $percentiles->fortyTime > 85:
                    $deep = $deep + 4;
                    break;
                case $percentiles->fortyTime > 75:
                    $deep = $deep + 3;
                    break;
                case $percentiles->fortyTime > 65:
                    $deep = $deep + 2;
                    break;
                case $percentiles->fortyTime > 55:
                    $deep = $deep + 1;
                    break;
                default:
                    $deep;
            }

            switch (true) {
                case $percentiles->jumpball > 95:
                    $deep = $deep + 5;
                    break;
                case $percentiles->jumpball > 85:
                    $deep = $deep + 4;
                    break;
                case $percentiles->jumpball > 75:
                    $deep = $deep + 3;
                    break;
                case $percentiles->jumpball > 65:
                    $deep = $deep + 2;
                    break;
                case $percentiles->jumpball > 55:
                    $deep = $deep + 1;
                    break;
                default:
                    $deep;
            }

            if ($percentiles->bully > 60) {
                $deep = $deep + 1;
            }

            if ($percentiles->agility > 70) {
                $deep = $deep + 1;
            }

            $data["deep"] = $deep;
            $data["slot"] = $slot;

            if ($wr['college_stats'] != null) {
                $college = $this->makeCollegeScore($wr);
                $data['collegeScore'] = $college['collegeScore'];
            } else {
                $data['collegeScore'] = null;
            }

            $alphaScore = ($data['collegeScore'] * 1.5) + ($data['deep'] * .6) + ($data['slot'] * .4);

            // Penalties
            if ($percentiles->agility < 35) {
                $alphaScore = $alphaScore - 1;
            }

//            if ($wr['percent.agility'] < 20) {
//                $alphaScore = $alphaScore - 3;
//            }

            if ($percentiles->jumpball < 40) {
                $alphaScore = $alphaScore - 2;
            }

            if ($percentiles->jumpball < 25) {
                $alphaScore = $alphaScore - 3;
            }

            if ($percentiles->fortyTime < 35) {
                $alphaScore = $alphaScore - 2;
            }

            if ($percentiles->fortyTime < 25) {
                $alphaScore = $alphaScore - 3;
            }

            $data['alpha'] = round($alphaScore,2);
            $data['yacScore'] = $yacScore;

            $jsonString = "";
            foreach ($data as $key => $value) {
                if ($value < 0) {
                    $value = 0;
                }
                $jsonString .= ", '$.{$key}', '{$value}'";
            }

            $update = <<<EOT
UPDATE player_test SET metrics = json_set(metrics{$jsonString}) where id = {$wr['id']};
EOT;
            $stmt = $this->db->query($update);
            $stmt->execute();
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Special Metrics completed\n";
    }

    public function makeCollegeScore($wr)
    {
        //get breakout score
        $collegeStats = json_decode($wr['college_stats']);
        $i = 0;
        $breakout = false;
        $collegeScore = 0;
        $bestDominator = .01;
        $bestSeason = [];
        $lastYear = "";
        $breakoutClass = "None";
        $bestReturn = 0;

        foreach ($collegeStats as $stats) {
            // get best college season
            if ($stats->year != "Career") {
                $tdDominator = round($stats->recTds / $stats->totals->tds * 100, 2);
                $ydsDominator = round($stats->recYds / $stats->totals->yds * 100, 2);

                $currentDominator = (array_sum([$ydsDominator, $tdDominator])) / 2;
                if ($currentDominator > $bestDominator) {
                    $bestDominator = $currentDominator;
                    $bestSeason = $stats;
                    $bestSeason->ydsDominator = $ydsDominator;
                    $bestSeason->tdsDominator = $tdDominator;
                }

                if ($stats->returnStats->puntYds > 0) {
                    $bestReturn = $stats->returnStats->puntYds + $stats->returnStats->kickYds;
                }

                if ($stats->ydsDominator > 20 && $tdDominator > 20) {
                    if ($breakout == false) {
                        if ($i == 0 && $stats->class == "FR") {
                            $collegeScore = 5 + $collegeScore;
                        } elseif ($i == 1) {
                            $collegeScore = 3 + $collegeScore;
                        } elseif ($i == 2) {
                            $collegeScore = 2 + $collegeScore;
                        } else {
                            $collegeScore = 1 + $collegeScore;
                        }
                        $breakoutClass = $stats->class;
                        $breakout = true;
                    }
                }

                $lastYear = $stats->class;
                $i++;
            }
        }

        // Coming out as a junior
        if ($lastYear !== "SR" && $i < 4) {
            $collegeScore = $collegeScore + 1;
        }

        // Best breakout score
        switch ($bestDominator) {
            case $bestDominator >= 50:
                $collegeScore = $collegeScore + 7;
                break;
            case $bestDominator >= 40:
                $collegeScore = $collegeScore + 6;
                break;
            case $bestDominator >= 35:
                $collegeScore = $collegeScore + 4;
                break;
            case $bestDominator >= 30:
                $collegeScore = $collegeScore + 3;
                break;
            case $bestDominator >= 25:
                $collegeScore = $collegeScore + 2;
                break;
            case $bestDominator >= 20:
                $collegeScore = $collegeScore + 1;
                break;
            default:
        }

        if ($bestReturn != 0) {
            switch ($bestReturn) {
                case $bestReturn > 1000:
                    $collegeScore = $collegeScore + 4;
                    break;
                case $bestReturn > 750:
                    $collegeScore = $collegeScore + 3;
                    break;
                case $bestReturn > 500:
                    $collegeScore = $collegeScore + 2;
                    break;
                case $bestReturn > 250:
                    $collegeScore = $collegeScore + 1;
                    break;
                default:
            }
        }

        return [
            'collegeScore' => $collegeScore,
            'bestSeason' => $bestSeason,
            'bestReturn' => $bestReturn,
            'breakoutClass' => $breakoutClass,
        ];

    }
}

class SqlWrCommand
{

    /**
     * @param string $type
     * @return mixed
     */


    public function calculateSpecialPercentiles()
    {
        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.jukeAgility'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.jukeAgility'),4,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.jukeAgility' != '0' AND position = 'WR'
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $jukeAgility = [];
        foreach($resultSet as $row) {
            $jukeAgility[$row->id] = $row->percentile_rank;
        }
        print "juke index built\n";

        /**************************************************************************/
        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.routeAgility'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.routeAgility'),4,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.routeAgility' != '0' AND position = 'WR'
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $routeAgility = [];
        foreach($resultSet as $row) {
            $routeAgility[$row->id] = $row->percentile_rank;
        }
        print "route index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.alpha', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.alpha'),3),6,'0') ASC),3) percentile_rank
FROM player_test
WHERE metrics->'$.alpha' IS NOT NULL AND metrics->'$.alpha' != '-'AND position = 'WR'
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $alpha = [];
        foreach($resultSet as $row) {
            $alpha[$row->id] = $row->percentile_rank;
        }
        print "alpha index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.slot', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.slot'),3),6,'0') ASC),3) percentile_rank
FROM player_test
WHERE metrics->'$.slot' IS NOT NULL AND metrics->'$.slot' != '-'AND position = 'WR'
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $slot = [];
        foreach($resultSet as $row) {
            $slot[$row->id] = $row->percentile_rank;
        }
        print "slot index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.deep', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.deep'),3), 6, '0') ASC),3) percentile_rank
FROM player_test
WHERE metrics->'$.deep' IS NOT NULL AND metrics->'$.deep' != '-'AND position = 'WR'
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $deep = [];
        foreach($resultSet as $row) {
            $deep[$row->id] = $row->percentile_rank;
        }
        print "deep index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.collegeScore', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.collegeScore'),3),6,'0') ASC),3) percentile_rank
FROM player_test
WHERE metrics->'$.collegeScore' IS NOT NULL AND metrics->'$.collegeScore' != '-'AND position = 'WR'
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $college = [];
        foreach($resultSet as $row) {
            $college[$row->id] = $row->percentile_rank;
        }
        print "collegeScore index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.yacScore', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.yacScore'),3),6,'0') ASC),3) percentile_rank
FROM player_test
WHERE metrics->'$.yacScore' IS NOT NULL AND metrics->'$.yacScore' != '-'AND position = 'WR'
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $yac = [];
        foreach($resultSet as $row) {
            $yac[$row->id] = $row->percentile_rank;
        }
        print "alpha index built\n";

        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where(['p.position = ?' => 'WR']);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $count = $resultSet->count();
        $wrs = $resultSet->toArray();
        print "Special Percentages starting\n";
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;
        foreach ($wrs as $wr) {
            $id = $wr['id'];
            $data['alpha'] = (array_key_exists($id, $alpha)) ? $alpha[$id] * 100 : "";
            $data['slot'] = (array_key_exists($id, $slot)) ? $slot[$id] * 100 : "";
            $data['deep'] = (array_key_exists($id, $deep)) ? $deep[$id] * 100 : "";
            $data['collegeScore'] = (array_key_exists($id, $college)) ? $college[$id] * 100 : "";
            $data['yacScore'] = (array_key_exists($id, $yac)) ? $yac[$id] * 100 : "";
            $data['jukeAgility'] = (array_key_exists($id, $jukeAgility)) ? $jukeAgility[$id] * 100 : "";
            $data['routeAgility'] = (array_key_exists($id, $routeAgility)) ? $routeAgility[$id] * 100 : "";

            $jsonString = "";
            foreach ($data as $key => $value) {
                $jsonString .= ", '$.{$key}', '{$value}'";
            }

            try {
                $update = <<<EOT
UPDATE player_test SET percentiles = json_set(percentiles{$jsonString}) where id = {$id};
EOT;
                $stmt   = $this->db->query($update);
                $playerUpdated = $stmt->execute();
            } catch (\Exception $exception) {
                $message = $exception->getMessage();
            }

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Special Percentages completed\n";
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

    public function makeCollegeScore($wr)
    {
        //get breakout score
        $collegeStats = json_decode($wr['college_stats']);
        $i = 0;
        $breakout = false;
        $collegeScore = 0;
        $bestDominator = .01;
        $bestSeason = [];
        $lastYear = "";
        $breakoutClass = "None";
        $bestReturn = 0;

        foreach ($collegeStats as $stats) {
            // get best college season
            if ($stats->year != "Career") {
                $tdDominator = round($stats->recTds / $stats->totals->tds * 100, 2);
                $ydsDominator = round($stats->recYds / $stats->totals->yds * 100, 2);

                $currentDominator = (array_sum([$ydsDominator, $tdDominator])) / 2;
                if ($currentDominator > $bestDominator) {
                    $bestDominator = $currentDominator;
                    $bestSeason = $stats;
                    $bestSeason->ydsDominator = $ydsDominator;
                    $bestSeason->tdsDominator = $tdDominator;
                }

                if ($stats->returnStats->puntYds > 0) {
                    $bestReturn = $stats->returnStats->puntYds + $stats->returnStats->kickYds;
                }

                if ($stats->ydsDominator > 20 && $tdDominator > 20) {
                    if ($breakout == false) {
                        if ($i == 0 && $stats->class == "FR") {
                            $collegeScore = 5 + $collegeScore;
                        } elseif ($i == 1) {
                            $collegeScore = 3 + $collegeScore;
                        } elseif ($i == 2) {
                            $collegeScore = 2 + $collegeScore;
                        } else {
                            $collegeScore = 1 + $collegeScore;
                        }
                        $breakoutClass = $stats->class;
                        $breakout = true;
                    }
                }

                $lastYear = $stats->class;
                $i++;
            }
        }

        // Coming out as a junior
        if ($lastYear !== "SR" && $i < 4) {
            $collegeScore = $collegeScore + 1;
        }

        // Best breakout score
        switch ($bestDominator) {
            case $bestDominator >= 50:
                $collegeScore = $collegeScore + 7;
                break;
            case $bestDominator >= 40:
                $collegeScore = $collegeScore + 6;
                break;
            case $bestDominator >= 35:
                $collegeScore = $collegeScore + 4;
                break;
            case $bestDominator >= 30:
                $collegeScore = $collegeScore + 3;
                break;
            case $bestDominator >= 25:
                $collegeScore = $collegeScore + 2;
                break;
            case $bestDominator >= 20:
                $collegeScore = $collegeScore + 1;
                break;
            default:
        }

        if ($bestReturn != 0) {
            switch ($bestReturn) {
                case $bestReturn > 1000:
                    $collegeScore = $collegeScore + 4;
                    break;
                case $bestReturn > 750:
                    $collegeScore = $collegeScore + 3;
                    break;
                case $bestReturn > 500:
                    $collegeScore = $collegeScore + 2;
                    break;
                case $bestReturn > 250:
                    $collegeScore = $collegeScore + 1;
                    break;
                default:
            }
        }

        return [
            'collegeScore' => $collegeScore,
            'bestSeason' => $bestSeason,
            'bestReturn' => $bestReturn,
            'breakoutClass' => $breakoutClass,
        ];

    }
}