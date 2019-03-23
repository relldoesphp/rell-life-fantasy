<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/22/19
 * Time: 7:59 AM
 */

namespace Player\Model;

use InvalidArgumentException;
use RuntimeException;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;

class SqlPlayerCommand implements PlayerCommandInterface
{

    private $db;

    public function __construct(AdapterInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function addPlayer(Player $player)
    {
        // TODO: Implement addPlayer() method.
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function updatePlayer(Player $player)
    {
        // TODO: Implement updatePlayer() method.
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function deletePlayer(Player $player)
    {
        // TODO: Implement deletePlayer() method.
    }

    /**
     * @param string $type
     * @return mixed
     */
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

            $sql = new Sql($this->db);
            $update = $sql->update('wr_metrics');
            $update->set($data);
            $update->where(['playerId = ?' => $player['playerId']]);

            $stmt   = $sql->prepareStatementForSqlObject($update);
            $playerUpdated = $stmt->execute();

        }

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
                $data['fortyTime'] = ($total / $count) * 100;
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
                $data['verticalJump'] = ($total / $count) * 100;
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
                $data['benchPress'] = ($total / $count) * 100;
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
                $data['broadJump'] = ($total / $count) * 100;
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
                $data['cone'] = ($total / $count) * 100;
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
                $data['shuttle'] = ($total / $count) * 100;
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
                $data['agility'] = ($total / $count) * 100;
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
                $data['jumpball'] = ($total / $count) * 100;
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
                $data['power'] = ($total / $count) * 100;
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
                $data['elusiveness'] = ($total / $count) * 100;
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


        }
    }


}