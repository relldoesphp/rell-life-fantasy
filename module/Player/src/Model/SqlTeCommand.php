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

class SqlTeCommand extends SqlPlayerAbstract
{
    private $db;
    private $consoleAdapter;

    public function __construct(AdapterInterface $db, Console $consoleAdapter)
    {
        parent::__construct($db, $consoleAdapter);
    }

    public function calculateMetrics($type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'players']);
        $select->join(['m' => 'wr_metrics'], 'p.id = m.playerId');
        $select->where(['p.position = ?' => 'WR']);
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
            if ($player['benchPress'] != null) {
                $data["bully"] = (1.87 * ($player['bmi'] - 26.6)) + $player['benchPress'];
            } else {
                $data["bully"] = null;
            }

            if ($player['shuttle'] != null && $player['cone'] != null) {
                $data['agility'] = $player["shuttle"] + $player["cone"];
            } else {
                $data['agility'] = null;
            }

            // each full pound worth .056 seconds
            // each full bmi unit worth .42 seconds
            // examples: Amari = 10.69, 10.1  JuJu = 11.07, 10.3 , Golden Tate = 11.46, 10.46, Mike Evans = 11, OBJ=10.21,
            if ($data['agility'] != null) {
                $data["elusiveness"] = $data['agility'] - (($player["bmi"] - 26.6) * .42);
            } else {
                $data["elusiveness"] = null;
            }

            // break tackle ability
            // each inch worth 1.69 broad jump
            // each pound over 200 worth .61 broad jump
            if ($player["broadJump"] != null) {
                $base = 200;
                switch($player['height']){
                    case $player['heightInches'] > 75:
                        $base = 221;
                        break;
                    case $player['heightInches'] > 74:
                        $base = 215;
                        break;
                    case $player['heightInches'] > 73:
                        $base = 210;
                        break;
                    case $player['heightInches'] > 72:
                        $base = 205;
                        break;
                    case $player['heightInches'] > 71:
                        $base = 195;
                        break;
                    default:
                }

                $weightBroad = ($player['weight']-$base) * .61;
                $heightBroad = ($player['heightInches'] - 72) * -3.2;
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
            $update = $sql->update('wr_metrics');
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
        $select->join(['m' => 'wr_metrics'], 'p.id = m.playerId');
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
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;
        foreach ($wrs as $wr) {
            //height percentile
            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'WR', 'heightInches < ?' => $wr['heightInches']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['height'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'WR', 'armsInches < ?' => $wr['armsInches']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['arms'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'WR', 'weight < ?' => $wr['weight']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['weight'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'WR', 'bmi < ?' => $wr['bmi']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['bmi'] = ($total / $count) * 100;

            $sql    = new Sql($this->db);
            $select = $sql->select('players');
            $select->where(['position = ?' => 'WR', 'hands < ?' => $wr['hands']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            $total = $result->count();
            $data['hands'] = ($total / $count) * 100;

            if ($wr['fortyTime'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['fortyTime > ?' => $wr['fortyTime']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['fortyTime'] = ($total / 790) * 100;
            } else {
                $data['fortyTime'] = null;
            }

            if ($wr['verticalJump'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['verticalJump < ?' => $wr['verticalJump']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['verticalJump'] = ($total / 774) * 100;
            } else {
                $data['verticalJump'] = null;
            }

            if ($wr['benchPress'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['benchPress < ?' => $wr['benchPress']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['benchPress'] = ($total / 688) * 100;
            } else {
                $data['benchPress'] = null;
            }

            if ($wr['broadJump'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['broadJump < ?' => $wr['broadJump']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['broadJump'] = ($total / 765) * 100;
            } else {
                $data['broadJump'] = null;
            }

            if ($wr['cone'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['cone > ?' => $wr['cone']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['cone'] = ($total / 722) * 100;
            } else {
                $data['cone'] = null;
            }

            if ($wr['shuttle'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['shuttle > ?' => $wr['shuttle']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['shuttle'] = ($total / 729) * 100;
            } else {
                $data['shuttle'] = null;
            }

            if ($wr['bully'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['bully < ?' => $wr['bully']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['bully'] = ($total / $count) * 100;
            } else {
                $data['bully'] = null;
            }

            if ($wr['agility'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['agility > ?' => $wr['agility']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['agility'] = ($total / 719) * 100;
            } else {
                $data['agility'] = null;
            }

            if ($wr['jumpball'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['jumpball < ?' => $wr['jumpball']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['jumpball'] = ($total / 774) * 100;
            } else {
                $data['jumpball'] = null;
            }

            if ($wr['power'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['power < ?' => $wr['power']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['power'] = ($total / 765) * 100;
            } else {
                $data['power'] = null;
            }

            if ($wr['elusiveness'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['elusiveness > ?' => $wr['elusiveness']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['elusiveness'] = ($total / 719) * 100;
            } else {
                $data['elusiveness'] = null;
            }

            if ($wr['collegeDominator'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['collegeDominator < ?' => $wr['collegeDominator']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['collegeDominator'] = ($total / $count) * 100;
            } else {
                $data['collegeDominator'] = null;
            }

            if ($wr['breakoutAge'] != null) {
                $sql    = new Sql($this->db);
                $select = $sql->select('wr_metrics');
                $select->where(['breakoutAge < ?' => $wr['breakoutAge']]);
                $stmt   = $sql->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data['breakoutAge'] = ($total / $count) * 100;
            } else {
                $data['breakoutAge'] = null;
            }

            $data['playerId'] = $wr['playerId'];
            $data['firstName'] = $wr['firstName'];
            $data['lastName'] = $wr['lastName'];
            $data['team'] = $wr['team'];

            //check for existing entry
            $sql    = new Sql($this->db);
            $select = $sql->select('wr_percentiles')->columns(['id']);
            $select->where(['playerId = ?' => $wr['playerId']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            if ($result->count() > 0) {
                $sql = new Sql($this->db);
                $update = $sql->update('wr_percentiles');
                $update->set($data);
                $update->where(['playerId = ?' => $wr['playerId']]);
                $stmt   = $sql->prepareStatementForSqlObject($update);
                $playerUpdated = $stmt->execute();
            } else {
                $sql = new Sql($this->db);
                $insert = $sql->insert('wr_percentiles');
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

            if (true) {
                $data2['yacScore'] = ($data['power'] * .35) + ($data['elusiveness'] *.65);
                $sql = new Sql($this->db);
                $update = $sql->update('wr_metrics');
                $update->set($data2);
                $update->where(['playerId = ?' => $wr['playerId']]);
                $stmt   = $sql->prepareStatementForSqlObject($update);
                $playerUpdated2 = $stmt->execute();

                $sql2    = new Sql($this->db);
                $select = $sql2->select('wr_metrics');
                $select->where(['yacScore < ?' => $data2['yacScore']]);
                $stmt   = $sql2->prepareStatementForSqlObject($select);
                $result = $stmt->execute();
                $total = $result->count();
                $data3['yacScore'] = ($total / $count) * 100;

                $sql = new Sql($this->db);
                $update = $sql->update('wr_percentiles');
                $update->set($data3);
                $update->where(['playerId = ?' => $wr['playerId']]);
                $stmt   = $sql->prepareStatementForSqlObject($update);
                $playerUpdated3 = $stmt->execute();

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
}