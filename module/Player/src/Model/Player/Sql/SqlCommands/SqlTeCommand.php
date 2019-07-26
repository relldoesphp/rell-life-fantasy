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

class SqlTeCommand extends SqlPlayerAbstract
{

    public function calculateSpecialScores($type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where(['p.position = ?' => 'TE']);
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

            if ($metrics->shuttle != null && $metrics->cone != null) {
                $data['jukeAgility'] = ($percentiles->shuttle * .70) + ($percentiles->cone * .30);
                $data['routeAgility'] = ($percentiles->shuttle * .30) + ($percentiles->cone * .70);
            } else {
                $data['jukeAgility'] = '';
                $data['routeAgility'] = '';
            }
            
            $data['runBlock'] = ($percentiles->bully * .30) + ($percentiles->power * .70);
            $data['passBlock'] = ($percentiles->bully * .40) + ($percentiles->elusiveness * .60);
            
            //Move - TE Speed + Jumpball + Route Agility
            $data['move'] = 0;
            switch ($metrics->fortyTime) {
                case $metrics->fortyTime < 4.55:
                    $data['move'] = $data["move"] + 7;
                    break;
                case $metrics->fortyTime < 4.60:
                    $data['move'] =  $data['move'] + 6;
                    break;
                case $metrics->fortyTime < 4.65:
                    $data['move'] = $data['move'] + 5;
                    break;
                case $metrics->fortyTime < 4.70:
                    $data['move'] = $data['move'] + 3;
                    break;
                case $metrics->fortyTime < 4.75:
                    $data['move'] = $data['move'] + 2;
                    break;
                case $metrics->fortyTime < 4.80:
                    $data['move'] = $data['move'] + 1;
                    break;
                default:
            }

            switch ($percentiles->jumpball) {
                case $percentiles->jumpball > 90:
                    $data['move'] = $data["move"] + 6;
                    break;
                case $percentiles->jumpball > 80:
                    $data['move'] =  $data['move'] + 5;
                    break;
                case $percentiles->jumpball > 70:
                    $data['move'] = $data['move'] + 4;
                    break;
                case $percentiles->jumpball > 60:
                    $data['move'] = $data['move'] + 3;
                    break;
                case $percentiles->jumpball > 50:
                    $data['move'] = $data['move'] + 2;
                    break;
                case $percentiles->jumpball > 40:
                    $data['move'] = $data['move'] + 1;
                    break;
                default:
            }

            switch ($data['routeAgility']) {
                case $data['routeAgility'] > 90:
                    $data['move'] = $data['move'] + 7;
                    break;
                case $data['routeAgility'] > 80:
                    $data['move'] = $data['move'] + 6;
                    break;
                case $data['routeAgility'] > 70:
                    $data['move'] = $data['move'] + 5;
                    break;
                case $data['routeAgility'] > 60:
                    $data['move'] = $data['move'] + 4;
                    break;
                case $data['routeAgility'] > 50:
                    $data['move'] = $data['move'] + 3;
                    break;
                case $data['routeAgility'] > 40:
                    $data['move'] = $data['move'] + 2;
                    break;
                default:
            }
            
            //Line -  TE Run Block + Pass Block + Weight
            $data['inLine'] = 0;
            switch ($data['runBlock']) {
                case $data['runBlock'] > 80:
                    $data['inLine'] = $data['inLine'] + 6;
                    break;
                case $data['runBlock'] > 70:
                    $data['inLine'] = $data['inLine'] + 5;
                    break;
                case $data['runBlock'] > 60:
                    $data['inLine'] = $data['inLine'] + 4;
                    break;
                case $data['runBlock'] > 50:
                    $data['inLine'] = $data['inLine'] + 3;
                    break;
                case $data['runBlock'] > 40:
                    $data['inLine'] = $data['inLine'] + 2;
                    break;
                default:
            }

            switch ($data['runBlock']) {
                case $data['runBlock'] > 80:
                    $data['inLine'] = $data['inLine'] + 6;
                    break;
                case $data['runBlock'] > 70:
                    $data['inLine'] = $data['inLine'] + 5;
                    break;
                case $data['runBlock'] > 60:
                    $data['inLine'] = $data['inLine'] + 4;
                    break;
                case $data['runBlock'] > 50:
                    $data['inLine'] = $data['inLine'] + 3;
                    break;
                case $data['runBlock'] > 40:
                    $data['inLine'] = $data['inLine'] + 2;
                    break;
                default:
            }

            switch ($info->weight) {
                case $info->weight > 265:
                    $data['inLine'] = $data["inLine"] + 7;
                    break;
                case $info->weight > 260:
                    $data['inLine'] =  $data['inLine'] + 5;
                    break;
                case $info->weight < 250:
                    $data['inLine'] = $data['inLine'] + 4;
                    break;
                case $info->weight < 245:
                    $data['inLine'] = $data['inLine'] + 3;
                    break;
                case $info->weight < 235:
                    $data['inLine'] = $data['inLine'] - 1;
                    break;
                default:
            }
            
            //Alpha -  Move+Line
            $data['alpha'] = $data['inLine'] + $data['move'];

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

    public function calculateSpecialPercentiles(){

        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.jukeAgility'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.jukeAgility'),4,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.jukeAgility' != '0' AND position = 'TE'
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
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.routeAgility'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.routeAgility'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.routeAgility' != '0' AND position = 'TE'
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
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.runBlock'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.runBlock'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.runBlock' != '0' AND position = 'TE'
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $runBlock = [];
        foreach($resultSet as $row) {
            $runBlock[$row->id] = $row->percentile_rank;
        }
        print "run Block built\n";
        /*****************************************************************************/
        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.passBlock'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.passBlock'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.passBlock' != '0' AND position = 'TE'
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $passBlock = [];
        foreach($resultSet as $row) {
            $passBlock[$row->id] = $row->percentile_rank;
        }
        print "pass Block built\n";


        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where(["position = ?" => 'TE']);
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
            $data['runBlock'] = (array_key_exists($id, $runBlock)) ? $runBlock[$id] * 100 : "";
            $data['passBlock'] = (array_key_exists($id, $passBlock)) ? $passBlock[$id] * 100 : "";

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
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'players']);
        $select->where([
            'p.position = ?' => 'TE',
            'collegeStats' => null
        ]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
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
        $request = new Request();
        if ($wr['cfb-alias'] == null) {
            $cfb = strtolower($wr['alias'])."-1";
        } else {
            $cfb = $wr['cfb-alias'];
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
//                if ($rowChildren->item(1)->nodeValue != $wr['college']) {
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
                $collegeStats[$year]['class'] = $rowChildren->item(3)->nodeValue;
                $collegeStats[$year]['games'] = $rowChildren->item(4)->nodeValue;
                $collegeStats[$year]['games'] = $rowChildren->item(5)->nodeValue;
                $collegeStats[$year]['receptions'] = $rowChildren->item(6)->nodeValue;
                $collegeStats[$year]['recYds'] = $rowChildren->item(7)->nodeValue;
                $collegeStats[$year]['recAvg'] = $rowChildren->item(8)->nodeValue;
                $collegeStats[$year]['recTds'] = $rowChildren->item(9)->nodeValue;
                $collegeStats[$year]['ydsDominator'] = (round($collegeStats[$year]['recYds'] / $totals['yds'], 4)) * 100;
                $collegeStats[$year]['recDominator'] = (round($collegeStats[$year]['receptions'] / $totals['recs'], 4)) * 100;
                $collegeStats[$year]['tdDominator'] = (round($collegeStats[$year]['recTds'] / $totals['tds'], 4)) * 100;
            }
            // $result is a DOMElement
        }

        $collegeJson = json_encode($collegeStats);

        $sql = new Sql($this->db);
        $update = $sql->update('players');
        $update->set(["collegeStats" => $collegeJson]);
        $update->where(['id = ?' => $wr['id']]);
        $stmt   = $sql->prepareStatementForSqlObject($update);
        $playerUpdated = $stmt->execute();
        return true;
    }
}