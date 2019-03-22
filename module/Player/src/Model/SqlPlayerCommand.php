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
    public function calculateMetrics(string $type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player']);
        $select->join(['m' => 'wr_metrics'], 'p.id = m.playerId');
        $select->where(['p.position = ?' => 'WR']);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        // (average bmi 26.6/ average bench 14.2) = 1.87
        // (1.87 * (wr bmi - average bmi)) + wr bench Press
        // examples: Miles Boykin = 12, Josh Doctson = 13, Josh Gordon 16, JuJu = 18.366, Chark = 12.787
        // strong examples: Julio = 19, Tate = 21.488, D.K. Metcalf = 30.553, Harry = 32, OBJ = 8.87,

        $wr["bully"] = (1.87 * ($wr['bmi'] - 26.6)) + $wr['benchPress'];

//        // beat press
//        if ($wr["benchPress"] == 0 || $wr["benchPress"] == null) {
//            $wr["benchPress"] = 14;
//        }
//        $data['beatPress'] = $wr["bmi"] + ($wr["benchPress"] * 1.89);

        // breakaway speed

        // shot-area quickness - route running
        $data['agility'] = $wr["20ydShuttle"] + $wr["3coneDrill"];
        // each full pound worth .056 seconds
        // each full bmi unit worth .42 seconds
        // examples: Amari = 10.69, 10.1  JuJu = 11.07, 10.3 , Golden Tate = 11.46, 10.46, Mike Evans = 11, OBJ=10.21,
        $data["elusiveness"] = $data['agility'] - (($wr["weight"] - 200) * .056);


        // break tackle ability
        // each inch worth 1.69 broad jump
        // each pound over 200 worth .61 broad jump
        $weightBroad = ($wr['weight']-215) * .55;
        $heightBroad = ($wr['height_inches'] - 70) * 1.68;
        //$data['adj_broadJump'] = $wr["broadJump"] - $heightBroad + $weightBroad;
        $data['rushing_power'] = $wr["broadJump"] - $heightBroad + $weightBroad;

        // contested catch ability
        if ($wr["vertical"] == 0 || $wr["vertical"] == null) {
            //if not vert assume average vertical
            $wr["vertical"] = 35.5;
        }

        $data['contested'] = $wr["height_inches"] + $wr["arms_inches"] + $wr["vertical"];
        $data['firstName'] = $wr["firstName"];
        $data['lastName'] = $wr['lastName'];

    }

    /**
     * @param string $type
     * @return mixed
     */
    public function calculatePercentiles(string $type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player']);
        $select->join(['m' => 'wr_metrics'], 'p.id = m.playerId');
        $select->where(['p.position = ?' => 'WR']);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $receivers = $resultSet->toArray();
    }


}