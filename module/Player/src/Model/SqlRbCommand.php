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

class SqlRbCommand extends SqlPlayerAbstract
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
        $select->join(['m' => 'rb_metrics'], 'p.id = m.playerId');
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
            if ($player['benchPress'] != null) {
                $data["bully"] = (1.53 * ($player['bmi'] - 30.27)) + $player['benchPress'];
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
                $data["elusiveness"] = $data['agility'] - (($player["bmi"] - 30.27) * .376);
            } else {
                $data["elusiveness"] = null;
            }

            // break tackle ability
            // each inch worth 1.69 broad jump
            // each pound over 200 worth .61 broad jump
            if ($player["broadJump"] != null) {
                $weightBroad = ($player['weight']-215) * .55;
                $heightBroad = ($player['heightInches'] - 70) * 1.68;
                $data['power'] = $player["broadJump"] - $heightBroad + $weightBroad;
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

            $data['firstName'] = $player['firstName'];
            $data['lastName'] = $player['lastName'];
            $data['team'] = $player['team'];

            $sql = new Sql($this->db);
            $update = $sql->update('rb_metrics');
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
        // TODO: Implement calculateSpecialScores() method.
    }


}