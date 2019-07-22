<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/9/19
 * Time: 12:19 AM
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
use Player\Model\Player\SqlCommands\SqlPlayerAbstract;

class SqlQBCommand extends SqlPlayerAbstract
{
    public function calculateSpecialScores()
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where->in("position", ["QB"]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $tes = $resultSet->toArray();
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;
        foreach ($tes as $te) {
            $info = json_decode($te['player_info']);
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

            $jsonString = "";
            foreach ($data as $key => $value) {
                if ($value < 0) {
                    $value = 0;
                }
                $jsonString .= ", '$.{$key}', '{$value}'";
            }

            $update = <<<EOT
UPDATE player_test SET metrics = json_set(metrics{$jsonString}) where id = {$te['id']};
EOT;
            $stmt = $this->db->query($update);
            $stmt->execute();
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function calculateSpecialPercentiles()
    {
        $sql = <<<EOT
        SELECT id, lpad(json_unquote(metrics->'$.wonderlic'),3,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.wonderlic'),3,'0')) percentile_rank
FROM player_test
WHERE json_unquote(metrics->'$.wonderlic') != '' AND position = 'QB'
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $wonderlic = [];
        foreach($resultSet as $row) {
            $wonderlic[$row->id] = $row->percentile_rank;
        }
        print "wonderlic built\n";
/****************************************/
        $sql = <<<EOT
        SELECT id, lpad(json_unquote(metrics->'$.throwVelocity'),3,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.throwVelocity'),3,'0')) percentile_rank
FROM player_test
WHERE json_unquote(metrics->'$.throwVelocity') != '' AND position = 'QB'
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $throwVelocity = [];
        foreach($resultSet as $row) {
            $throwVelocity[$row->id] = $row->percentile_rank;
        }
        print "throw velocity built\n";
        /****************************************/
        $sql = <<<EOT
SELECT id, lpad(json_unquote(metrics->'$.depthAdjPct'),5,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.depthAdjPct'),5,'0')) percentile_rank
FROM player_test
WHERE position = 'QB' AND json_unquote(metrics->'$.depthAdjPct') is not null AND json_unquote(metrics->'$.depthAdjPct') > 0
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $depthAdjPct = [];
        foreach($resultSet as $row) {
            $depthAdjPct[$row->id] = $row->percentile_rank;
        }
        print "depth adjusted PCT\n";

        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where->in("position", ["QB"]);
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
            $data['wonderlic'] = (array_key_exists($id, $wonderlic)) ? $wonderlic[$id] * 100 : "";
            $data['throwVelocity'] = (array_key_exists($id, $throwVelocity)) ? $throwVelocity[$id] * 100 : "";
            $data['depthAdjPct'] = (array_key_exists($id, $depthAdjPct)) ? $depthAdjPct[$id] * 100 : "";
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
where position = 'QB'
      and player_info->'$.active'
      and college_stats is null
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
        if (!array_key_exists("cfbAlias", $api)) {
            $cleanFirst = preg_replace('/[^A-Za-z0-9\-]/', '', $rb['first_name']);
            $cleanLast = preg_replace('/[^A-Za-z0-9\-]/', '', $rb['last_name']);
            $cfb = strtolower("{$cleanFirst}-{$cleanLast}")."-1";
        } else {
            $cfb = $api->cfbAlias;
        }

        $request->setUri("https://www.sports-reference.com/cfb/players/{$cfb}.html");

        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();

        $weird = strpos($html, '<div id="all_passing" class="table_wrapper table_controls">');
        $passHtml =  substr($html, $weird);

        $dom = new Query($passHtml);
        $results = $dom->execute('#passing tr');

        $count = count($results);
        if ($count == 0) {
            return false;
        }
        $collegeStats = [];
        foreach ($results as $result) {
            $rowChildren = $result->childNodes;
            $firstItem = $rowChildren->item(1)->nodeValue;

            if (!empty($firstItem) && $firstItem != 'Year' && $firstItem) {
                if ($firstItem == "Overall") {
                    break;
                }
                $year = $rowChildren->item(0)->nodeValue;
                $year = str_replace("*", "", $year);
                if (! $rowChildren->item(1)->firstChild instanceof \DOMElement) {
                    return false;
                }

                try {
                    $collegeHref = $rowChildren->item(1)->firstChild->getAttribute("href");
                    $totals = $this->getCollegeTotals($collegeHref);
                    $collegeStats[$year]['totals'] = $totals;
                    $collegeStats[$year]['year'] = $year;
                    $collegeStats[$year]['college'] = $rowChildren->item(1)->nodeValue;
                    $collegeStats[$year]['conf'] = $rowChildren->item(2)->nodeValue;
                    $collegeStats[$year]['class'] = $rowChildren->item(3)->nodeValue;
                    $collegeStats[$year]['position'] = $rowChildren->item(4)->nodeValue;
                    $collegeStats[$year]['games'] = $rowChildren->item(5)->nodeValue;
                    $collegeStats[$year]['cmp'] = $rowChildren->item(6)->nodeValue;
                    $collegeStats[$year]['att'] = $rowChildren->item(7)->nodeValue;
                    $collegeStats[$year]['pct'] = $rowChildren->item(8)->nodeValue;
                    $collegeStats[$year]['yds'] = $rowChildren->item(9)->nodeValue;
                    $collegeStats[$year]['ypa'] = $rowChildren->item(10)->nodeValue;
                    $collegeStats[$year]['aypa'] = $rowChildren->item(11)->nodeValue;
                    $collegeStats[$year]['tds'] = $rowChildren->item(12)->nodeValue;
                    $collegeStats[$year]['ints'] = $rowChildren->item(13)->nodeValue;
                    $collegeStats[$year]['rate'] = $rowChildren->item(14)->nodeValue;
                } catch (\Exception $e) {
                    $wrong = $e->getMessage();
                }

            }
            // $result is a DOMElement
        }
        $weird = strpos($html, '<div class="overthrow table_container" id="div_rushing">');
        $rushHtml =  substr($html, $weird);

        $dom = new Query($rushHtml);
        $results = $dom->execute('#rushing tr');

        $count = count($results);
        if ($count != 0) {
            foreach ($results as $result) {
                $rowChildren = $result->childNodes;
                $firstItem = $rowChildren->item(1)->nodeValue;
                if ($firstItem == "Overall") {
                    break;
                }
                if (!empty($firstItem) && $firstItem != 'Year' && $firstItem) {
                    $year = $rowChildren->item(0)->nodeValue;
                    $year = str_replace("*", "", $year);
                    if (! $rowChildren->item(1)->firstChild instanceof \DOMElement) {
                        return false;
                    }
                    $collegeStats[$year]['rushAtt'] = $rowChildren->item(6)->nodeValue;
                    $collegeStats[$year]['rushYds'] = $rowChildren->item(7)->nodeValue;
                    $collegeStats[$year]['rushAvg'] = $rowChildren->item(8)->nodeValue;
                    $collegeStats[$year]['rushTds'] = $rowChildren->item(9)->nodeValue;
                }
                // $result is a DOMElement
            }
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