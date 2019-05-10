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

class SqlRbCommand extends SqlPlayerAbstract
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
        $select->join(['metrics' => 'rb_metrics'], 'p.id = metrics.playerId', [
            'metrics.fortyTime' => 'fortyTime',
            'metrics.verticalJump' => 'verticalJump',
            'metrics.broadJump' => 'broadJump',
            'metrics.benchPress' => 'benchPress',
            'metrics.shuttle' => 'shuttle',
            'metrics.cone' => 'cone',
            'metrics.breakoutAge' => 'breakoutAge',
            'metrics.breakoutYear' => 'breakoutYear',
            'metrics.collegeDominator' => 'collegeDominator',
            'metrics.collegeYPC' => 'collegeYPC',
            'metrics.agility' => 'agility',
            'metrics.elusiveness' => 'elusiveness',
            'metrics.power' => 'power',
            'metrics.bully' => 'bully',
            'metrics.jumpball' => 'jumpball',
            'metrics.speedScore' => 'speedScore'
        ], Select::JOIN_LEFT);
        $select->join(['percent' => 'rb_percentiles'], 'p.id = percent.playerId', [
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
            'percent.collegeScore' => 'collegeScore'
        ], Select::JOIN_LEFT);
        $select->where(['p.position = ?' => 'RB']);
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
            // (average bmi 26.6/ average bench 14.2) = 1.87
            // (1.87 * (wr bmi - average bmi)) + wr bench Press
            if ($player['metrics.benchPress'] != null) {
                $data["bully"] = (1.53 * ($player['bmi'] - 30.27)) + $player['metrics.benchPress'];
            } else {
                $data["bully"] = null;
            }

            if ($player['metrics.shuttle'] != null && $player['metrics.cone'] != null) {
                $data['agility'] = $player["metrics.shuttle"] + $player["metrics.cone"];
            } else {
                $data['agility'] = null;
            }

            // each full pound worth .056 seconds
            // each full bmi unit worth .42 seconds
            // examples: Amari = 10.69, 10.1  JuJu = 11.07, 10.3 , Golden Tate = 11.46, 10.46, Mike Evans = 11, OBJ=10.21,
            if ($data['agility'] != null) {
                $data["elusiveness"] = $data['agility'] - (($player["bmi"] - 30.27) * .376);
            } else {
                $data["elusiveness"] = null;
            }

            // break tackle ability
            // each inch worth 1.69 broad jump
            // each pound over 200 worth .61 broad jump
            if ($player["metrics.broadJump"] != null) {
                $weightBroad = ($player['weight']-215) * .55;
                $heightBroad = ($player['heightInches'] - 70) * 1.68;
                $data['power'] = $player["metrics.broadJump"] - $heightBroad + $weightBroad;
            } else {
                $data['power'] = null;
            }

            // add jumpball reach
            if ($player["metrics.verticalJump"] != null) {
                $data['jumpball'] = $player["heightInches"] + $player["armsInches"] + $player["metrics.verticalJump"];
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

            //add speed score
            if ($player["metrics.fortyTime"] != null) {
                $data["speedScore"] = ($player['weight'] * 200)/pow($player['metrics.fortyTime'], 4);
            }

            $data['firstName'] = $player['firstName'];
            $data['lastName'] = $player['lastName'];
            $data['team'] = $player['team'];

            $sql = new Sql($this->db);
            $update = $sql->update('rb_metrics');
            $update->set($data);
            $update->where(['playerId = ?' => $player['id']]);

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
        $select->join(['m' => 'rb_metrics'], 'p.id = m.playerId');
        $select->where(['p.position = ?' => 'RB']);
        $stmt   = $sql->prepareStatementForSqlObject($select);
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
            //height percentile
            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'RB', 'heightInches < ?' => $rb['heightInches']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['height'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'RB', 'armsInches < ?' => $rb['armsInches']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['arms'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'RB', 'weight < ?' => $rb['weight']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['weight'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'RB', 'bmi < ?' => $rb['bmi']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['bmi'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'RB', 'hands < ?' => $rb['hands']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['hands'] = ($total / $count) * 100;

            if ($rb['fortyTime'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['fortyTime > ?' => $rb['fortyTime']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['fortyTime'] = ($total / $count) * 100;
            } else {
                $data['fortyTime'] = null;
            }

            if ($rb['verticalJump'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['verticalJump < ?' => $rb['verticalJump']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['verticalJump'] = ($total / $count) * 100;
            } else {
                $data['verticalJump'] = null;
            }

            if ($rb['benchPress'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['benchPress < ?' => $rb['benchPress']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['benchPress'] = ($total / $count) * 100;
            } else {
                $data['benchPress'] = null;
            }

            if ($rb['broadJump'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['broadJump < ?' => $rb['broadJump']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['broadJump'] = ($total / $count) * 100;
            } else {
                $data['broadJump'] = null;
            }

            if ($rb['cone'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['cone > ?' => $rb['cone']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['cone'] = ($total / $count) * 100;
            } else {
                $data['cone'] = null;
            }

            if ($rb['shuttle'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['shuttle > ?' => $rb['shuttle']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['shuttle'] = ($total / $count) * 100;
            } else {
                $data['shuttle'] = null;
            }

            if ($rb['bully'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['bully < ?' => $rb['bully']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['bully'] = ($total / $count) * 100;
            } else {
                $data['bully'] = null;
            }

            if ($rb['agility'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['agility > ?' => $rb['agility']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['agility'] = ($total / $count) * 100;
            } else {
                $data['agility'] = null;
            }

            if ($rb['jumpball'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['jumpball < ?' => $rb['jumpball']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['jumpball'] = ($total / $count) * 100;
            } else {
                $data['jumpball'] = null;
            }

            if ($rb['power'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['power < ?' => $rb['power']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['power'] = ($total / $count) * 100;
            } else {
                $data['power'] = null;
            }

            if ($rb['elusiveness'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['elusiveness > ?' => $rb['elusiveness']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['elusiveness'] = ($total / $count) * 100;
            } else {
                $data['elusiveness'] = null;
            }

            if ($rb['collegeDominator'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['collegeDominator < ?' => $rb['collegeDominator']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['collegeDominator'] = ($total / $count) * 100;
            } else {
                $data['collegeDominator'] = null;
            }

            if ($rb['breakoutAge'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['breakoutAge < ?' => $rb['breakoutAge']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['breakoutAge'] = ($total / $count) * 100;
            } else {
                $data['breakoutAge'] = null;
            }

            if ($rb['speedScore'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('rb_metrics');
                $select->where(['speedScore < ?' => $rb['speedScore']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['speedScore'] = ($total / $count) * 100;
            } else {
                $data['speedScore'] = null;
            }

            $data['playerId'] = $rb['playerId'];
            $data['firstName'] = $rb['firstName'];
            $data['lastName'] = $rb['lastName'];
            $data['team'] = $rb['team'];

            //check for existing entry
            $sql    = new Sql($this->db);
            $select = $sql->select('rb_percentiles')->columns(['id']);
            $select->where(['playerId = ?' => $rb['playerId']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            if ($result->count() > 0) {
                $sql = new Sql($this->db);
                $update = $sql->update('rb_percentiles');
                $update->set($data);
                $update->where(['playerId = ?' => $rb['playerId']]);
                $stmt   = $sql->prepareStatementForSqlObject($update);
                $playerUpdated = $stmt->execute();
            } else {
                $sql = new Sql($this->db);
                $insert = $sql->insert('rb_percentiles');
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

    public function calculateSpecialScores($type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'players']);
        $select->join(['metrics' => 'rb_metrics'], 'p.id = metrics.playerId', [
            'metrics.fortyTime' => 'fortyTime',
            'metrics.verticalJump' => 'verticalJump',
            'metrics.broadJump' => 'broadJump',
            'metrics.benchPress' => 'benchPress',
            'metrics.shuttle' => 'shuttle',
            'metrics.cone' => 'cone',
            'metrics.breakoutAge' => 'breakoutAge',
            'metrics.breakoutYear' => 'breakoutYear',
            'metrics.agility' => 'agility',
            'metrics.elusiveness' => 'elusiveness',
            'metrics.power' => 'power',
            'metrics.bully' => 'bully',
            'metrics.jumpball' => 'jumpball'
        ], Select::JOIN_LEFT);
        $select->join(['percent' => 'rb_percentiles'], 'p.id = percent.playerId', [
            'percent.fortyTime' => 'fortyTime',
            'percent.verticalJump' => 'verticalJump',
            'percent.broadJump' => 'broadJump',
            'percent.benchPress' => 'benchPress',
            'percent.shuttle' => 'shuttle',
            'percent.cone' => 'cone',
            'percent.breakoutAge' => 'breakoutAge',
            'percent.agility' => 'agility',
            'percent.elusiveness' => 'elusiveness',
            'percent.power' => 'power',
            'percent.bully' => 'bully',
            'percent.jumpball' => 'jumpball',
            'percent.collegeScore' => 'collegeScore'
        ], Select::JOIN_LEFT);
        $select->where(['p.position = ?' => 'RB']);
        $sqlString = $select->getSqlString();
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
            $data['passCatcher'] = 0;
            $data['grinder'] = 0;
            /*  scat back score
                1.) Speed  4
                2.) Agility 3
                3.) College Reception share 3
                4.) Elusiveness 2
            */

            $bestRecDom = 0;
            $bestYPC = 0;
            $collegeStats = json_decode($rb['collegeStats']);

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

            switch ($rb['metrics.fortyTime']) {
                case $rb['metrics.fortyTime'] < 4.41:
                    $data['passCatcher'] =  $data['passCatcher'] + 5;
                    break;
                case $rb['metrics.fortyTime'] < 4.46:
                    $data['passCatcher'] =  $data['passCatcher'] + 4;
                    break;
                case $rb['metrics.fortyTime'] < 4.51:
                    $data['passCatcher'] = $data['passCatcher'] + 3;
                    break;
                case $rb['metrics.fortyTime'] < 4.56:
                    $data['passCatcher'] = $data['passCatcher'] + 2;
                    break;
                case $rb['metrics.fortyTime'] < 4.62:
                    $data['passCatcher'] = $data['passCatcher'] + 1;
                    break;
                case $rb['metrics.fortyTime'] < 4.70:
                    $data['passCatcher'] = $data['passCatcher'] - 2;
                    break;
                case $rb['metrics.fortyTime'] < 4.80:
                    $data['passCatcher'] = $data['passCatcher'] - 3;
                    break;
                default:
            }

            switch ($rb['metrics.agility']) {
                case $rb['metrics.agility'] < 10.80:
                    $data['passCatcher'] = $data['passCatcher'] + 5;
                    break;
                case $rb['metrics.agility'] < 11.10:
                    $data['passCatcher'] = $data['passCatcher'] + 4;
                    break;
                case $rb['metrics.agility'] < 11.20:
                    $data['passCatcher'] = $data['passCatcher'] + 3;
                    break;
                case $rb['metrics.agility'] < 11.30:
                    $data['passCatcher'] = $data['passCatcher'] + 2;
                    break;
                case $rb['metrics.agility'] < 11.60:
                    $data['passCatcher'] = $data['passCatcher'] + 1;
                    break;
                default:
            }

            switch ($rb['percent.power']) {
                case $rb['percent.power'] > 99:
                    break;
                case $rb['percent.power'] > 90:
                    $data['grinder'] =  $data['grinder'] + 8;
                    break;
                case $rb['percent.power'] > 80:
                    $data['grinder'] =  $data['grinder'] + 7;
                    break;
                case $rb['percent.power'] > 70:
                    $data['grinder'] =  $data['grinder'] + 5;
                    break;
                case $rb['percent.power'] > 60:
                    $data['grinder'] =  $data['grinder'] + 3;
                    break;
                case $rb['percent.power'] > 20:
                    $data['grinder'] =  $data['grinder'] + 1;
                    break;
                default:
            }

            switch ($rb['percent.elusiveness']) {
                case $rb['percent.elusiveness'] > 99:
                    break;
                case $rb['percent.elusiveness'] > 90:
                    $data['grinder'] =  $data['grinder'] + 6;
                    break;
                case $rb['percent.elusiveness'] > 80:
                    $data['grinder'] =  $data['grinder'] + 5;
                    break;
                case $rb['percent.elusiveness'] > 70:
                    $data['grinder'] =  $data['grinder'] + 4;
                    break;
                case $rb['percent.elusiveness'] > 60:
                    $data['grinder'] =  $data['grinder'] + 3;
                    break;
                case $rb['percent.elusiveness'] > 50:
                    $data['grinder'] =  $data['grinder'] + 2;
                    break;
                case $rb['percent.elusiveness'] > 20:
                    $data['grinder'] =  $data['grinder'] + 1;
                    break;
                default:
            }

            $data['alpha'] = $data['passCatcher'] + $data['grinder'];

            $sql = new Sql($this->db);
            $update = $sql->update('rb_metrics');
            $update->set($data);
            $update->where(['playerId = ?' => $rb['id']]);
            $stmt   = $sql->prepareStatementForSqlObject($update);
            $playerUpdated = $stmt->execute();

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

    public function scrapCollegeJob()
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'players']);
        $select->where([
            'p.position = ?' => 'RB',
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
        $request = new Request();
        if ($rb['cfb-alias'] == null) {
            $cfb = strtolower($rb['alias'])."-1";
        } else {
            $cfb = $rb['cfb-alias'];
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
                if ($rowChildren->item(1)->nodeValue != $rb['college']) {
                    return false;
                }
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

        $collegeJson = json_encode($collegeStats);

        $sql = new Sql($this->db);
        $update = $sql->update('players');
        $update->set(["collegeStats" => $collegeJson]);
        $update->where(['id = ?' => $rb['id']]);
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