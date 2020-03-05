<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/22/19
 * Time: 7:59 AM
 */

namespace Player\Model\Player\Sql;

use InvalidArgumentException;
use Player\Model\Player\SqlCommands\SqlQBCommand;
use RuntimeException;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\ProgressBar\ProgressBar;
use Laminas\ProgressBar\Adapter\Console;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Update;
use Laminas\Http\Request;
use Laminas\Http\Client;
use Laminas\Dom\Query;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\Player;

class SqlPlayerCommand implements PlayerCommandInterface
{

    private $db;
    private $consoleAdapter;
    private $qbCommand;
    private $wrCommand;
    private $rbCommand;
    private $teCommand;
    private $olCommand;
    private $dlCommand;
    private $olbCommand;
    private $ilbCommand;
    private $cbCommand;
    private $fsCommand;
    private $ssCommand;

    public function __construct(AdapterInterface $db, Console $consoleAdapter)
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
    }

    public function save(Player $player)
    {
        if ($player->getId() != null) {
            $this->updatePlayer($player);
        } else {
            $this->addPlayer($player);
        }
    }


    public function addPlayer(Player $player)
    {
        $player->encodeJson();

        /** Insert new player **/
        $sql    = new Sql($this->db);
        $insert = $sql->insert('player_test');
        $insert->values([
            'first_name' => $player->getFirstName(),
            'last_name' => $player->getLastName(),
            'sleeper_id' => $player->getSleeperId(),
            'search_full_name' => $player->getSearchFullName(),
            'position' => $player->getPosition(),
            'team' => $player->getTeam(),
            'player_info' => $player->getPlayerInfo(),
            'team_info' => $player->getTeamInfo(),
            'api_info' => $player->getApiInfo(),
            'injury_info' => $player->getInjuryInfo(),
            'metrics' => $player->getMetrics(),
            'percentiles' => $player->getPercentiles(),
            'college_stats' => $player->getCollegeStats(),
            'images' => $player->getImages(),
        ]);

        $stmt = $sql->prepareStatementForSqlObject($insert);
        try {
            $result = $stmt->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function updatePlayer(Player $player)
    {
        $player->encodeJson();

        /** Update player **/
        $sql    = new Sql($this->db);
        $update = $sql->update('player_test');
        $update->set([
            'first_name' => $player->getFirstName(),
            'last_name' => $player->getLastName(),
            'search_full_name' => $player->getSearchFullName(),
            'position' => $player->getPosition(),
            'team' => $player->getTeam(),
            'player_info' => $player->getPlayerInfo(),
            'team_info' => $player->getTeamInfo(),
            'api_info' => $player->getApiInfo(),
            'injury_info' => $player->getInjuryInfo(),
            'metrics' => $player->getMetrics(),
            'percentiles' => $player->getPercentiles(),
            'college_stats' => $player->getCollegeStats(),
            'images' => $player->getImages(),
        ]);
        $update->where(['id = ?' => $player->getId()]);
        $stmt   = $sql->prepareStatementForSqlObject($update);
       // $result = $stmt->execute();
        try {
            $result = $stmt->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function deletePlayer(Player $player)
    {
        // TODO: Implement deletePlayer() method.
    }

    public function getQbCommand()
    {
        return $this->qbCommand;
    }

    public function getWrCommand()
    {
        return $this->wrCommand;
    }

    public function getRbCommand()
    {
        return $this->rbCommand;
    }

    public function getTeCommand()
    {
        return $this->teCommand;
    }

    public function getOlCommand()
    {
        return $this->olCommand;
    }

    public function getDlCommand()
    {
        return $this->dlCommand;
    }

    public function getOlbCommand()
    {
        return $this->olbCommand;
    }

    public function getIlbCommand()
    {
        return $this->ilbCommand;
    }

    public function getCbCommand()
    {
        return $this->cbCommand;
    }

    public function getFsCommand()
    {
        return $this->fsCommand;
    }

    public function getSsCommand()
    {
        return $this->ssCommand;
    }


    public function makePlayerNameJson()
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('player_test')->columns([
            'id',
            'first_name',
            'last_name',
            'search_full_name',
            'position',
            'team',
            'full_name' => new Expression("concat(first_name,' ',last_name,' ',position,' ',team)"),
            "nohash" => new Expression("Replace(json_unquote(player_info->'$.hashtag'),'#','')")
        ]);

        if (!empty($type)) {
            $select->where(['position = ?' => $type]);
        }

        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $json = json_encode($resultSet->toArray());
    }



    public function makeJsonString($array=[])
    {
        $jsonString = "";
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
                $jsonString .= ", '$.{$key}', '{$value}'";
            } else {
                $jsonString .= ", '$.{$key}', '{$value}'";
            }
        }
        $jsonString = ltrim($jsonString, ',');

        return $jsonString;
    }
}