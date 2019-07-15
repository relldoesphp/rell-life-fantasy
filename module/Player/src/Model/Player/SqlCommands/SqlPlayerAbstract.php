<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 4/6/19
 * Time: 11:55 AM
 */

namespace Player\Model\Player\SqlCommands;

use InvalidArgumentException;
use RuntimeException;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\ProgressBar\ProgressBar;
use Zend\ProgressBar\Adapter\Console;
use Zend\Db\Sql\Select;



class SqlPlayerAbstract
{
    public $db;
    public $consoleAdapter;

    public function __construct(AdapterInterface $db, Console $consoleAdapter)
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
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
            ],
            'RB' => [
                'bmiAvg' => 30.27,
                'weightAvg' => 210.94,
                'heightAvg' => 70.57,
                'benchAvg' => 18.11,
                'broadAvg' => 113.43,
                'agilityAvg' => 11.21,
            ],
            'WR' => [
                'bmiAvg' => 26.6,
                'weightAvg' => 196.08,
                'heightAvg' => 72.6,
                'benchAvg' => 12.29,
                'broadAvg' => 116.39,
                'agilityAvg' => 11.14,
            ],
            'TE' => [
                'bmiAvg' => 30.22,
                'weightAvg' => 248.48,
                'heightAvg' => 76.46,
                'benchAvg' => 17.6,
                'broadAvg' => 116.83,
                'agilityAvg' => 11.47,
            ],
            'OL' => [
                'bmiAvg' => 37.12,
                'weightAvg' => 309.23,
                'heightAvg' => 76.71,
                'benchAvg' => 22.05,
                'broadAvg' => 104.25,
                'agilityAvg' => 6.51 + 4.05,
            ],
            'DL' => [
                'bmiAvg' => 35.37,
                'weightAvg' => 282.59,
                'heightAvg' => 75.48,
                'benchAvg' => 23.3,
                'broadAvg' => 111.54,
                'agilityAvg' => 6.69 + 4.14,
            ],
            'OLB' => [
                'bmiAvg' => 31.13,
                'weightAvg' => 241.15,
                'heightAvg' => 74.28,
                'benchAvg' => 20.36,
                'broadAvg' => 119.17,
                'agilityAvg' => 6.69 + 4.14,
            ],
            'ILB' => [
                'bmiAvg' => 31.04,
                'weightAvg' => 236.36,
                'heightAvg' => 73.29,
                'benchAvg' => 23.3,
                'broadAvg' => 111.54,
                'agilityAvg' => 6.69 + 4.14,
            ],
            'CB' => [
                'bmiAvg' => 26.58,
                'weightAvg' => 191.2,
                'heightAvg' => 71.42,
                'benchAvg' => 23.3,
                'broadAvg' => 111.54,
                'agilityAvg' => 6.69 + 4.14,
            ],
            'FS' => [
                'bmiAvg' => 27.57,
                'weightAvg' => 202.22,
                'heightAvg' => 72.04,
                'benchAvg' => 23.3,
                'broadAvg' => 111.54,
                'agilityAvg' => 6.69 + 4.14,
            ],
            'SS' => [
                'bmiAvg' => 28.14,
                'weightAvg' => 205.05,
                'heightAvg' => 71.59,
                'benchAvg' => 23.3,
                'broadAvg' => 111.54,
                'agilityAvg' => 6.69 + 4.14,
            ],
        ];

        $where = [];


        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);

        switch ($type) {
            case "OL":
                $select->where->in("position", ["C","G","OT"]);
                break;
            case "DL":
                $select->where->in("position", ['DT','NT','DE']);
                break;
            default:
                $select->where(["position = ?" => $type]);
        }


        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $players = $resultSet->toArray();
        print "Metrics started\n";
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;

        foreach ($players as $player) {
            $info = json_decode($player['player_info']);
            $metrics = json_decode($player['metrics']);

            $data = [];

            // (average bmi 26.6/ average bench 14.2) = 1.87
            // (1.87 * (wr bmi - average bmi)) + wr bench Press
            if ($type == 'CB') {
                $metrics->benchPress = 14;
            }

            if ($metrics->benchPress != null && $metrics->benchPress != '-') {
                $data["$.bully"] = round((($posInfo[$type]['bmiAvg']/$posInfo[$type]['benchAvg']) * ($info->bmi - $posInfo[$type]['bmiAvg'])) + $metrics->benchPress, 2);
            } else {
                $data["$.bully"] = null;
            }

            if ($metrics->shuttle != null && $metrics->cone != null) {
                $data['$.agility'] = $metrics->shuttle + $metrics->cone;
            } else {
                $data['$.agility'] = null;
            }

            // each full pound worth .056 seconds
            // each full bmi unit worth .42 seconds
            // examples: Amari = 10.69, 10.1  JuJu = 11.07, 10.3 , Golden Tate = 11.46, 10.46, Mike Evans = 11, OBJ=10.21,
            // agility
            if ($data['$.agility'] != null) {
                $els = $posInfo[$type]['agilityAvg']/$posInfo[$type]['bmiAvg'];
                $data["$.elusiveness"] = round(($data['$.agility'] - (($info->bmi - $posInfo[$type]['bmiAvg']) * $els)), 2);
            } else {
                $data["$.elusiveness"] = null;
            }

            // break tackle ability
            // each inch worth 1.68 broad jump
            // each pound over 215 worth .55 broad jump
            if ($metrics->broadJump != null) {
                $weightAdj = $posInfo[$type]['broadAvg']/$posInfo[$type]['weightAvg'];
                $heightAdj = $posInfo[$type]['broadAvg']/$posInfo[$type]['heightAvg'];
                $weightBroad = ($info->weight - $posInfo[$type]['weightAvg']) * $weightAdj;
                $heightBroad = ($info->heightInches - $posInfo[$type]['heightAvg']) * $heightAdj;
                $data['$.power'] = round(($metrics->broadJump - $heightBroad + $weightBroad),2);
            } else {
                $data['$.power'] = null;
            }

            // add jumpball reach
            if ($metrics->verticalJump != null) {
                $data['$.jumpball'] = $info->heightInches + $info->armsInches + $metrics->verticalJump;
                // Premium for big Hands
                if ($info->hands > 9.5) {
                    $data["$.jumpball"] = $data["$.jumpball"] + 3;
                }
                if ($info->hands > 9.99) {
                    $data["$.jumpball"] = $data["$.jumpball"] + 2;
                }

            } else {
                $data['$.jumpball'] = null;
            }

            //add Weight Speed Score
            if ($metrics->fortyTime != null) {
                $data['$.speedScore'] = round((($info->weight * $posInfo[$type]['weightAvg'])/(pow($metrics->fortyTime,4))), 2);
            }

            $jsonString = "";
            foreach ($data as $key => $value) {
                $jsonString .= ", '{$key}', '{$value}'";
            }

//            $sql = new Sql($this->db);
//            $update = $sql->update('player_test');
//            $update->set(['metrics' => new Expression("json_set(metrics{$jsonString})")]);
//            $update->where('id = ?', $player['id']);
//            $stmt = $sql->prepareStatementForSqlObject($update);
//            $playerUpdated = $stmt->execute();
//            $pointer++;
//            $progressBar->update($pointer);


            $update = <<<EOT
UPDATE player_test SET metrics = json_set(metrics{$jsonString}) where id = {$player['id']};
EOT;

            $stmt   = $this->db->query($update);
            $playerUpdated = $stmt->execute();
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Metrics completed\n";
    }

    public function calculatePercentiles($type)
    {
        $where = [];
        switch ($type) {
            case "OL":
                $where = "position IN ('G','C','OT')";
                break;
            case "DL":
                $where = "position IN ('DT','NT','DE')";
                break;
            default:
                $where = "position = '{$type}'";
        }

        // build result sets
        $sql = <<<EOT
SELECT id, ROUND(PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(player_info->'$.heightInches'),6,'0')),3) percentile_rank
FROM player_test
WHERE player_info->'$.heightInches' IS NOT NULL
AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $height = [];
        foreach($resultSet as $row) {
            $height[$row->id] = $row->percentile_rank;
        }
        print "height index built\n";
        /**************************************************************************/

        $sql = <<<EOT
SELECT id, ROUND(PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(player_info->'$.armsInches'),6,'0')),3) percentile_rank
FROM player_test
WHERE player_info->'$.armsInches' IS NOT NULL
AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $arms = [];
        foreach($resultSet as $row) {
            $arms[$row->id] = $row->percentile_rank;
        }
        print "arms index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, ROUND(PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(player_info->'$.weight'),3,'0')),3) percentile_rank
FROM player_test
WHERE player_info->'$.weight' IS NOT NULL
AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $weight = [];
        foreach($resultSet as $row) {
            $weight[$row->id] = $row->percentile_rank;
        }
        print "weight index built\n";
        /**************************************************************************/

        $sql = <<<EOT
SELECT id, ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(player_info->'$.bmi'),3),6,'0')),3) percentile_rank
FROM player_test
WHERE player_info->'$.bmi' IS NOT NULL
AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $bmi = [];
        foreach($resultSet as $row) {
            $bmi[$row->id] = $row->percentile_rank;
        }
        print "bmi index built\n";
        /**************************************************************************/

        $sql = <<<EOT
SELECT id, ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(player_info->'$.hands'),3),6,'0')),3) percentile_rank
FROM player_test
WHERE player_info->'$.hands' IS NOT NULL
AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $hands = [];
        foreach($resultSet as $row) {
            $hands[$row->id] = $row->percentile_rank;
        }
        print "hands index built\n";
        /**************************************************************************/

        $sql = <<<EOT
SELECT id, metrics->'$.fortyTime', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.fortyTime'),3),6,'0') DESC),3) percentile_rank
FROM player_test
WHERE metrics->'$.fortyTime' IS NOT NULL AND metrics->'$.fortyTime' != '-'AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $fortyTime = [];
        foreach($resultSet as $row) {
            $fortyTime[$row->id] = $row->percentile_rank;
        }
        print "40 index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.cone', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.cone'),3),6,'0') DESC),3) percentile_rank
FROM player_test
WHERE metrics->'$.cone' IS NOT NULL AND metrics->'$.cone' != '-'AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $cone = [];
        foreach($resultSet as $row) {
            $cone[$row->id] = $row->percentile_rank;
        }
        print "cone index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.shuttle', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.shuttle'),3),6,'0') DESC),3) percentile_rank
FROM player_test
WHERE metrics->'$.shuttle' IS NOT NULL AND metrics->'$.shuttle' != '-'AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $shuttle = [];
        foreach($resultSet as $row) {
            $shuttle[$row->id] = $row->percentile_rank;
        }
        print "shuttle index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.benchPress', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.benchPress'),3),6,'0')),3) percentile_rank
FROM player_test
WHERE metrics->'$.benchPress' IS NOT NULL AND metrics->'$.benchPress' != '-'AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $benchPress = [];
        foreach($resultSet as $row) {
            $benchPress[$row->id] = $row->percentile_rank;
        }
        print "benchPress index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.verticalJump', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.verticalJump'),3),6,'0')),3) percentile_rank
FROM player_test
WHERE metrics->'$.verticalJump' IS NOT NULL AND metrics->'$.verticalJump' != '-'AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $verticalJump = [];
        foreach($resultSet as $row) {
            $verticalJump[$row->id] = $row->percentile_rank;
        }
        print "vertical index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.broadJump', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.broadJump'),3),6,'0')),3) percentile_rank
FROM player_test
WHERE metrics->'$.broadJump' IS NOT NULL AND metrics->'$.broadJump' != '-' AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $broadJump = [];
        foreach($resultSet as $row) {
            $broadJump[$row->id] = $row->percentile_rank;
        }
        print "broad index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.agility', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.agility'),3),6,'0') DESC),3) percentile_rank
FROM player_test
WHERE metrics->'$.agility' IS NOT NULL AND metrics->'$.agility' != '0' AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $agility = [];
        foreach($resultSet as $row) {
            $agility[$row->id] = $row->percentile_rank;
        }
        print "agility index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.elusiveness', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.elusiveness'),3),6,'0') DESC),3) percentile_rank
FROM player_test
WHERE metrics->'$.elusiveness' IS NOT NULL AND metrics->'$.elusiveness' != '0' AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $elusiveness = [];
        foreach($resultSet as $row) {
            $elusiveness[$row->id] = $row->percentile_rank;
        }
        print "elusiveness index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.jumpball', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.jumpball'),3),6,'0')),3) percentile_rank
FROM player_test
WHERE metrics->'$.jumpball' IS NOT NULL AND metrics->'$.jumpball' != ''AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $jumpball = [];
        foreach($resultSet as $row) {
            $jumpball[$row->id] = $row->percentile_rank;
        }
        print "jumpball index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.bully', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.bully'),3),6,'0')),2) percentile_rank
FROM player_test
WHERE metrics->'$.bully' IS NOT NULL AND metrics->'$.bully' != '0'AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $bully = [];
        foreach($resultSet as $row) {
            $bully[$row->id] = $row->percentile_rank;
        }
        print "bully index built\n";
        /**************************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.power', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.power'),3),6,'0')),3) percentile_rank
FROM player_test
WHERE metrics->'$.power' IS NOT NULL AND metrics->'$.power' != '0'AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $power = [];
        foreach($resultSet as $row) {
            $power[$row->id] = $row->percentile_rank;
        }
        print "power index built\n";

        /*********************************************************************/
        $sql = <<<EOT
SELECT id, metrics->'$.speedScore', ROUND(PERCENT_RANK() OVER (ORDER BY lpad(round(json_unquote(metrics->'$.speedScore'),3),8,'0')),3) percentile_rank
FROM player_test
WHERE metrics->'$.speedScore' IS NOT NULL AND metrics->'$.speedScore' != '-'AND {$where}
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $speedScore = [];
        foreach($resultSet as $row) {
            $speedScore[$row->id] = $row->percentile_rank;
        }
        print "SpeedScore index built\n";


        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        switch ($type) {
            case "OL":
                $select->where->in("position", ["C","G","OT"]);
                break;
            case "DL":
                $select->where->in("position", ['DT','NT','DE']);
                break;
            default:
                $select->where(["position = ?" => $type]);
        }
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
            $data['heightInches'] = (array_key_exists($id, $height)) ? $height[$id] * 100 : "";
            $data['arms'] = (array_key_exists($id, $arms)) ? $arms[$id] * 100 : "";
            $data['weight'] = (array_key_exists($id, $weight)) ? $weight[$id] * 100 : "";
            $data['bmi'] = (array_key_exists($id, $bmi)) ? $bmi[$id] * 100 : "";
            $data['hands'] = (array_key_exists($id, $hands)) ? $hands[$id] * 100 : "";
            $data['fortyTime'] = (array_key_exists($id, $fortyTime)) ? $fortyTime[$id] * 100 : "";
            $data['shuttle'] = (array_key_exists($id, $shuttle)) ? $shuttle[$id] * 100 : "";
            $data['cone'] = (array_key_exists($id, $cone)) ? $cone[$id] * 100 : "";
            $data['benchPress'] = (array_key_exists($id, $benchPress)) ? $benchPress[$id] * 100 : "";
            $data['broadJump'] = (array_key_exists($id, $broadJump)) ? $broadJump[$id] * 100 : "";
            $data['verticalJump'] = (array_key_exists($id, $verticalJump)) ? $verticalJump[$id] * 100 : "";
            $data['agility'] = (array_key_exists($id, $agility)) ? $agility[$id] * 100 : "";
            $data['bully'] = (array_key_exists($id, $bully)) ? $bully[$id] * 100 : "";
            $data['power'] = (array_key_exists($id, $power)) ? $power[$id] * 100 : "";
            $data['elusiveness'] = (array_key_exists($id, $elusiveness)) ? $elusiveness[$id] * 100 : "";
            $data['jumpball'] = (array_key_exists($id, $jumpball)) ? $jumpball[$id] * 100 : "";
            $data['speedScore'] = (array_key_exists($id, $speedScore)) ? $speedScore[$id] * 100 : "";

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
        return $total;
    }

}