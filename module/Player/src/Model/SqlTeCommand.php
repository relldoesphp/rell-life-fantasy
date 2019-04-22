<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 4/5/19
 * Time: 11:34 PM
 */

namespace Player\Model;

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
    private $db;
    private $consoleAdapter;

    public function __construct(AdapterInterface $db, Console $consoleAdapter)
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
    }

    public function calculateMetrics($type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'players']);
        $select->join(['m' => 'te_metrics'], 'p.id = m.playerId');
        $select->where(['p.position = ?' => 'TE']);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $players = $resultSet->toArray();
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;

        foreach ($players as $player) {
            $data = [];
            // (average bmi 30.15/ average bench 19.76) = 1.526
            // (1.87 * (wr bmi - average bmi)) + wr bench Press
            if ($player['benchPress'] != null) {
                $data["bully"] = (1.526 * ($player['bmi'] - 30.15)) + $player['benchPress'];
            } else {
                $data["bully"] = null;
            }

            if ($player['shuttle'] != null && $player['cone'] != null) {
                $data['agility'] = $player["shuttle"] + $player["cone"];
            } else {
                $data['agility'] = null;
            }

            // each full pound worth .046 seconds
            // each full bmi unit worth .38 seconds
            // examples: Amari = 10.69, 10.1  JuJu = 11.07, 10.3 , Golden Tate = 11.46, 10.46, Mike Evans = 11, OBJ=10.21,
            if ($data['agility'] != null) {
                $data["elusiveness"] = $data['agility'] - (($player["bmi"] - 30.15) * .38);
            } else {
                $data["elusiveness"] = null;
            }

            // break tackle ability
            // each inch worth 1.69 broad jump
            // each pound over 250 worth .464 broad jump
            if ($player["broadJump"] != null) {
                $base = 250;
//                switch($player['height']){
//                    case $player['heightInches'] > 75:
//                        $base = 221;
//                        break;
//                    case $player['heightInches'] > 74:
//                        $base = 215;
//                        break;
//                    case $player['heightInches'] > 73:
//                        $base = 210;
//                        break;
//                    case $player['heightInches'] > 72:
//                        $base = 205;
//                        break;
//                    case $player['heightInches'] > 71:
//                        $base = 195;
//                        break;
//                    default:
//                }

                $weightBroad = ($player['weight']-$base) * .46;
//                $heightBroad = ($player['heightInches'] - 72) * -3.2;
                $data['power'] = $player["broadJump"] + $weightBroad;
            } else {
                $data['power'] = null;
            }

            // add jumpball reach
            if ($player["verticalJump"] != null) {
                $data['jumpball'] = $player["heightInches"] + $player["armsInches"] + $player["verticalJump"];
                // Premium for big Hands
                if ($player["hands"] > 9.5) {
                    $data["jumpball"] = $data["jumpball"] + 3;
                }
                if ($player["hands"] > 9.99) {
                    $data["jumpball"] = $data["jumpball"] + 2;
                }

            } else {
                $data['jumpball'] = null;
            }

            $sql = new Sql($this->db);
            $update = $sql->update('te_metrics');
            $update->set($data);
            $update->where(['playerId = ?' => $player['playerId']]);

            $stmt   = $sql->prepareStatementForSqlObject($update);
            $playerUpdated = $stmt->execute();
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    /**
     * @param string $type
     * @return mixed
     */
    public function calculatePercentiles($type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'players']);
        $select->join(['m' => 'te_metrics'], 'p.id = m.playerId');
        $select->where(['p.position = ?' => 'TE']);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $count = $resultSet->count();
        $tes = $resultSet->toArray();
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;
        foreach ($tes as $te) {
            //height percentile 
            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'TE', 'heightInches < ?' => $te['heightInches']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['height'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'TE', 'armsInches < ?' => $te['armsInches']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['arms'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'TE', 'weight < ?' => $te['weight']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['weight'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'TE', 'bmi < ?' => $te['bmi']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['bmi'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'TE', 'hands < ?' => $te['hands']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['hands'] = ($total / $count) * 100;

            if ($te['fortyTime'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['fortyTime > ?' => $te['fortyTime']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['fortyTime'] = ($total / $count) * 100;
            } else {
                $data['fortyTime'] = null;
            }

            if ($te['verticalJump'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['verticalJump < ?' => $te['verticalJump']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['verticalJump'] = ($total / $count) * 100;
            } else {
                $data['verticalJump'] = null;
            }

            if ($te['benchPress'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['benchPress < ?' => $te['benchPress']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['benchPress'] = ($total / $count) * 100;
            } else {
                $data['benchPress'] = null;
            }

            if ($te['broadJump'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['broadJump < ?' => $te['broadJump']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['broadJump'] = ($total / $count) * 100;
            } else {
                $data['broadJump'] = null;
            }

            if ($te['cone'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['cone > ?' => $te['cone']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['cone'] = ($total / $count) * 100;
            } else {
                $data['cone'] = null;
            }

            if ($te['shuttle'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['shuttle > ?' => $te['shuttle']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['shuttle'] = ($total / $count) * 100;
            } else {
                $data['shuttle'] = null;
            }

            if ($te['bully'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['bully < ?' => $te['bully']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['bully'] = ($total / $count) * 100;
            } else {
                $data['bully'] = null;
            }

            if ($te['agility'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['agility > ?' => $te['agility']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['agility'] = ($total / $count) * 100;
            } else {
                $data['agility'] = null;
            }

            if ($te['jumpball'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['jumpball < ?' => $te['jumpball']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['jumpball'] = ($total / $count) * 100;
            } else {
                $data['jumpball'] = null;
            }

            if ($te['power'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['power < ?' => $te['power']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['power'] = ($total / $count) * 100;
            } else {
                $data['power'] = null;
            }

            if ($te['elusiveness'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['elusiveness > ?' => $te['elusiveness']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['elusiveness'] = ($total / $count) * 100;
            } else {
                $data['elusiveness'] = null;
            }

            if ($te['collegeDominator'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['collegeDominator < ?' => $te['collegeDominator']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['collegeDominator'] = ($total / $count) * 100;
            } else {
                $data['collegeDominator'] = null;
            }

            if ($te['breakoutAge'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('te_metrics');
                $select->where(['breakoutAge < ?' => $te['breakoutAge']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['breakoutAge'] = ($total / $count) * 100;
            } else {
                $data['breakoutAge'] = null;
            }

            $data['playerId'] = $te['playerId'];
            $data['firstName'] = $te['firstName'];
            $data['lastName'] = $te['lastName'];
            $data['team'] = $te['team'];

            //check for existing entry
            $sql    = new Sql($this->db);
            $select = $sql->select('te_percentiles')->columns(['id']);
            $select->where(['playerId = ?' => $te['playerId']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            if ($result->count() > 0) {
                $sql = new Sql($this->db);
                $update = $sql->update('te_percentiles');
                $update->set($data);
                $update->where(['playerId = ?' => $te['playerId']]);
                $stmt   = $sql->prepareStatementForSqlObject($update);
                $playerUpdated = $stmt->execute();
            } else {
                $sql = new Sql($this->db);
                $insert = $sql->insert('te_percentiles');
                $insert->columns([
                    'playerId',
                    'team',
                    'height',
                    'arms',
                    'weight',
                    'bmi',
                    'hands',
                    'fortyTime',
                    'verticalJump',
                    'broadJump',
                    'shuttle',
                    'cone',
                    'bully',
                    'agility',
                    'elusiveness',
                    'power',
                    'jumpball',
                    'collegeDominator',
                    'breakoutAge',
                    'firstName',
                    'lastName'
                ]);
                $insert->values($data);
                $stmt   = $sql->prepareStatementForSqlObject($insert);
                $result = $stmt->execute();
                $result->count();
            }

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function calculateSpecialScores()
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'players']);
        $select->join(['metrics' => 'wr_metrics'], 'p.id = metrics.playerId', [
            'metrics.fortyTime' => 'fortyTime',
            'metrics.verticalJump' => 'verticalJump',
            'metrics.broadJump' => 'broadJump',
            'metrics.benchPress' => 'benchPress',
            'metrics.shuttle' => 'shuttle',
            'metrics.cone' => 'cone',
            'metrics.breakoutAge' => 'breakoutAge',
            'metrics.breakoutYear' => 'breakoutYear',
            'metrics.collegeDominator' => 'collegeDominator',
            'metrics.collegeYPR' => 'collegeYPR',
            'metrics.agility' => 'agility',
            'metrics.elusiveness' => 'elusiveness',
            'metrics.power' => 'power',
            'metrics.bully' => 'bully',
            'metrics.jumpball' => 'jumpball',
            'metrics.yacScore' => 'yacScore'
        ], Select::JOIN_LEFT);
        $select->join(['percent' => 'wr_percentiles'], 'p.id = percent.playerId', [
            'percent.fortyTime' => 'fortyTime',
            'percent.verticalJump' => 'verticalJump',
            'percent.broadJump' => 'broadJump',
            'percent.benchPress' => 'benchPress',
            'percent.shuttle' => 'shuttle',
            'percent.cone' => 'cone',
            'percent.breakoutAge' => 'breakoutAge',
            'percent.collegeDominator' => 'collegeDominator',
            'percent.collegeYPR' => 'collegeYPR',
            'percent.agility' => 'agility',
            'percent.elusiveness' => 'elusiveness',
            'percent.power' => 'power',
            'percent.bully' => 'bully',
            'percent.jumpball' => 'jumpball',
            'percent.yacScore' => 'yacScore'
        ], Select::JOIN_LEFT);
        $select->where(['p.position = ?' => 'WR']);
        $sqlString = $select->getSqlString();
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $wrs = $resultSet->toArray();
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;
        foreach ($wrs as $wr) {
            //slot score
            $slot = 0;
            switch ($wr['percent.agility']) {
                case $wr['percent.agility'] > 85:
                    $slot = $slot + 5;
                    break;
                case $wr['percent.agility'] > 75:
                    $slot = $slot + 4;
                    break;
                case $wr['percent.agility'] > 60:
                    $slot = $slot + 3;
                    break;
                case $wr['percent.agility'] > 50:
                    $slot = $slot + 2;
                    break;
                case $wr['percent.agility'] > 40:
                    $slot = $slot + 1;
                    break;
                default:
                    $slot;
            }

            switch ($wr['metrics.yacScore']) {
                case $wr['metrics.yacScore'] > 85:
                    $slot = $slot + 5;
                    break;
                case $wr['metrics.yacScore'] > 75:
                    $slot = $slot + 4;
                    break;
                case $wr['metrics.yacScore'] > 60:
                    $slot = $slot + 3;
                    break;
                case $wr['metrics.yacScore'] > 50:
                    $slot = $slot + 2;
                    break;
                case $wr['metrics.yacScore'] > 40:
                    $slot = $slot + 1;
                    break;
                default:
                    $slot;
            }

            if ($wr['heightInches'] > 73) {
                $slot = $slot + 1;
            }

            $deep = 0;

            switch (true) {
                case $wr['percent.fortyTime'] > 95:
                    $deep = $deep + 7;
                    break;
                case $wr['percent.fortyTime'] > 90:
                    $deep = $deep + 6;
                    break;
                case $wr['percent.fortyTime'] > 85:
                    $deep = $deep + 4;
                    break;
                case $wr['percent.fortyTime'] > 75:
                    $deep = $deep + 3;
                    break;
                case $wr['percent.fortyTime'] > 65:
                    $deep = $deep + 2;
                    break;
                case $wr['percent.fortyTime'] > 55:
                    $deep = $deep + 1;
                    break;
                default:
                    $deep;
            }

            switch (true) {
                case $wr['percent.jumpball'] > 95:
                    $deep = $deep + 5;
                    break;
                case $wr['percent.jumpball'] > 85:
                    $deep = $deep + 4;
                    break;
                case $wr['percent.jumpball'] > 75:
                    $deep = $deep + 3;
                    break;
                case $wr['percent.jumpball'] > 65:
                    $deep = $deep + 2;
                    break;
                case $wr['percent.jumpball'] > 55:
                    $deep = $deep + 1;
                    break;
                default:
                    $deep;
            }

            if ($wr['percent.bully'] > 60) {
                $deep = $deep + 1;
            }

            if ($wr['percent.agility'] > 70) {
                $deep = $deep + 1;
            }

            $data["deep"] = $deep;
            $data["slot"] = $slot;

            $sql = new Sql($this->db);
            $update = $sql->update('wr_metrics');
            $update->set($data);
            $update->where(['playerId = ?' => $wr['id']]);
            $stmt   = $sql->prepareStatementForSqlObject($update);
            $playerUpdated = $stmt->execute();

            $data2 = [];
            if ($data['slot'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['slot < ?' => $data['slot']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data2['slot'] = ($total / 850) * 100;
            } else {
                $data2['slot'] = null;
            }

            if ($data['deep'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['deep < ?' => $data['deep']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data2['deep'] = ($total / 850) * 100;
            } else {
                $data2['deep'] = null;
            }

            $sql = new Sql($this->db);
            $update = $sql->update('wr_percentiles');
            $update->set($data2);
            $update->where(['playerId = ?' => $wr['id']]);
            $stmt   = $sql->prepareStatementForSqlObject($update);
            $playerUpdated2 = $stmt->execute();

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
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