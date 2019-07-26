<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 4/5/19
 * Time: 11:34 PM
 */

namespace Player\Model\Player\SqlCommands;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\ProgressBar\ProgressBar;
use Zend\ProgressBar\Adapter\Console;
use Zend\Db\Sql\Select;
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Dom\Query;

class SqlRbCommand extends SqlPlayerAbstract
{

    public function calculateSpecialScores($type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where(['p.position = ?' => 'RB']);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $rbs = $resultSet->toArray();
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;
        foreach ($rbs as $rb) {
            $info = json_decode($rb['player_info']);
            $metrics = json_decode($rb['metrics']);
            $percentiles = json_decode($rb['percentiles']);

            if ($metrics->shuttle != null && $metrics->cone != null) {
                $data['jukeAgility'] = ($percentiles->shuttle * .70) + ($percentiles->cone * .30);
                $data['routeAgility'] = ($percentiles->shuttle * .30) + ($percentiles->cone * .70);
            } else {
                $data['jukeAgility'] = '';
                $data['routeAgility'] = '';
            }

            $data['passCatcher'] = 0;
            $data['grinder'] = 0;
            /*  scat back score
                1.) Speed  4
                2.) Agility 3
                3.) College Reception share 3
                4.) Elusiveness 2
            */
            $collegeStats = json_decode($rb['college_stats']);
            $bestRecDom = 0;
            $bestYPC = 0;
            if ($collegeStats != null) {
                foreach ($collegeStats as $stats) {
                    if ($stats->recDominator > $bestRecDom) {
                        $bestRecDom = $stats->recDominator;
                    }

                    if ($stats->rushAvg > $bestYPC) {
                        $bestYPC = $stats->rushAvg;
                    }
                }

                switch ($bestRecDom) {
                    case $bestRecDom > 20:
                        $data['passCatcher'] = $data['passCatcher'] + 8;
                        break;
                    case $bestRecDom > 15:
                        $data['passCatcher'] = $data['passCatcher'] + 6;
                        break;
                    case $bestRecDom > 10:
                        $data['passCatcher'] = $data['passCatcher'] + 4;
                        break;
                    case $bestRecDom > 9:
                        $data['passCatcher'] = $data['passCatcher'] + 3;
                        break;
                    case $bestRecDom > 8:
                        $data['passCatcher'] = $data['passCatcher'] + 2;
                        break;
                    case $bestRecDom > 6:
                        $data['passCatcher'] = $data['passCatcher'] - 1;
                        break;
                    case $bestRecDom > 4:
                        $data['passCatcher'] = $data['passCatcher'] - 2;
                        break;
                    case $bestRecDom > 2:
                        $data['passCatcher'] = $data['passCatcher'] - 3;
                        break;
                    default:
                }

                switch ($bestYPC) {
                    case $bestYPC > 7:
                        $data['grinder'] = $data['grinder'] + 3;
                        break;
                    case $bestYPC > 6:
                        $data['grinder'] = $data['grinder'] + 2;
                        break;
                    case $bestYPC > 5:
                        $data['grinder'] = $data['grinder'] + 1;
                        break;
                    case $bestYPC > 4:
                        $data['grinder'] = $data['grinder'] - 1;
                        break;
                    case $bestYPC > 3:
                        $data['grinder'] = $data['grinder'] - 2;
                        break;
                    case $bestYPC > 2:
                        $data['grinder'] = $data['grinder'] - 4;
                        break;
                    default:
                }
            }

            switch ($metrics->fortyTime) {
                case $metrics->fortyTime < 4.41:
                    $data['passCatcher'] =  $data['passCatcher'] + 5;
                    break;
                case $metrics->fortyTime < 4.46:
                    $data['passCatcher'] =  $data['passCatcher'] + 4;
                    break;
                case $metrics->fortyTime < 4.51:
                    $data['passCatcher'] = $data['passCatcher'] + 3;
                    break;
                case $metrics->fortyTime < 4.56:
                    $data['passCatcher'] = $data['passCatcher'] + 2;
                    break;
                case $metrics->fortyTime < 4.62:
                    $data['passCatcher'] = $data['passCatcher'] + 1;
                    break;
                case $metrics->fortyTime < 4.70:
                    $data['passCatcher'] = $data['passCatcher'] - 2;
                    break;
                case $metrics->fortyTime < 4.80:
                    $data['passCatcher'] = $data['passCatcher'] - 3;
                    break;
                default:
            }

            switch ($metrics->agility) {
                case $metrics->agility < 10.80:
                    $data['passCatcher'] = $data['passCatcher'] + 5;
                    break;
                case $metrics->agility < 11.10:
                    $data['passCatcher'] = $data['passCatcher'] + 4;
                    break;
                case $metrics->agility < 11.20:
                    $data['passCatcher'] = $data['passCatcher'] + 3;
                    break;
                case $metrics->agility < 11.30:
                    $data['passCatcher'] = $data['passCatcher'] + 2;
                    break;
                case $metrics->agility < 11.60:
                    $data['passCatcher'] = $data['passCatcher'] + 1;
                    break;
                default:
            }

            switch ($percentiles->power) {
                case $percentiles->power > 99:
                    break;
                case $percentiles->power > 90:
                    $data['grinder'] =  $data['grinder'] + 8;
                    break;
                case $percentiles->power > 80:
                    $data['grinder'] =  $data['grinder'] + 7;
                    break;
                case $percentiles->power > 70:
                    $data['grinder'] =  $data['grinder'] + 5;
                    break;
                case $percentiles->power > 60:
                    $data['grinder'] =  $data['grinder'] + 3;
                    break;
                case $percentiles->power > 20:
                    $data['grinder'] =  $data['grinder'] + 1;
                    break;
                default:
            }

            switch ($percentiles->elusiveness) {
                case $percentiles->elusiveness > 99:
                    break;
                case $percentiles->elusiveness > 90:
                    $data['grinder'] =  $data['grinder'] + 6;
                    break;
                case $percentiles->elusiveness > 80:
                    $data['grinder'] =  $data['grinder'] + 5;
                    break;
                case $percentiles->elusiveness > 70:
                    $data['grinder'] =  $data['grinder'] + 4;
                    break;
                case $percentiles->elusiveness > 60:
                    $data['grinder'] =  $data['grinder'] + 3;
                    break;
                case $percentiles->elusiveness > 50:
                    $data['grinder'] =  $data['grinder'] + 2;
                    break;
                case $percentiles->elusiveness > 20:
                    $data['grinder'] =  $data['grinder'] + 1;
                    break;
                default:
            }

            $data['alpha'] = $data['passCatcher'] + $data['grinder'];

            $jsonString = "";
            foreach ($data as $key => $value) {
                if ($value < 0) {
                    $value = 0;
                }
                $jsonString .= ", '$.{$key}', '{$value}'";
            }

            $update = <<<EOT
UPDATE player_test SET metrics = json_set(metrics{$jsonString}) where id = {$rb['id']};
EOT;
            $stmt = $this->db->query($update);
            $stmt->execute();
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
            /* grinder score
                1.) Power 4
                2.) Elusivenss 3
                3.) College YPC 3
                4.) Bench Press 1
            */

            /* Alpha score
                1.) College Score 5
                2.) Scat Back Score 4
                3.) Grinder Score 3
            */
    }

    public function calculateSpecialPercentiles(){

        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.jukeAgility'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.jukeAgility'),4,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.jukeAgility' != '0' AND position = 'RB'
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
WHERE metrics->'$.routeAgility' != '0' AND position = 'RB'
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

        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where(["position = ?" => 'RB']);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $count = $resultSet->count();
        $players = $resultSet->toArray();
        print "Percentages started\n";
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;
        foreach ($players as $player) {
            $id = $player['id'];
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
        print "Percentages completed\n";
    }




    public function scrapCollegeJob()
    {
        $sql =<<<EOT
Select * from player_test
where position = 'RB'
      and player_info->'$.active'
      and college_stats is null
      and team is not null
      and json_unquote(player_info->'$.college') not in ('-', 'None', 'null', 'No College')
EOT;

        $stmt   = $this->db->query($sql);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $count = $resultSet->count();
        $rbs = $resultSet->toArray();

        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;

        foreach ($rbs as $rb) {
            $result = $this->scrapCollegeStats($rb);
            if ($result == false) {
                $pointer++;
                $progressBar->update($pointer);
                continue;
            }
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
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