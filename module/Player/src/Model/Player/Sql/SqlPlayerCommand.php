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
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\ProgressBar\ProgressBar;
use Zend\ProgressBar\Adapter\Console;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Dom\Query;
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
//        $this->qbCommand = new SqlCommaSqlQBCommand($db, $consoleAdapter);
//        $this->wrCommand = new SqlCommands\SqlWrCommand($db, $consoleAdapter);
//        $this->rbCommand = new SqlRbCommand($db, $consoleAdapter);
//        $this->teCommand = new SqlTeCommand($db, $consoleAdapter);
//        $this->olCommand = new SqlOlCommand($db, $consoleAdapter);
//        $this->dlCommand = new SqlDlCommand($db, $consoleAdapter);
//        $this->olbCommand = new SqlOLBCommand($db, $consoleAdapter);
//        $this->ilbCommand = new SqlILBCommand($db, $consoleAdapter);
//        $this->cbCommand = new SqlCBCommand($db, $consoleAdapter);
//        $this->fsCommand = new SqlFSCommand($db, $consoleAdapter);
//        $this->ssCommand = new SqlSSCommand($db, $consoleAdapter);
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

    public function getSleeperStats()
    {
        $request = new Request();
        $uri = "https://api.sleeper.app/v1/stats/nfl/regular/2016";
        $request->setUri($uri);

        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();

        $json = (array) json_decode($html);

        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($json));
        $pointer = 0;
        foreach ($json as $key => $value) {
            $data = [];

            $sql = new Sql($this->db);
            $insert = $sql->insert('season_stats');
            $data = [
                'sleeper_id' => $key,
                'year' => '2016'
            ];

            if (!empty((array) $value)) {
                $data['stats'] = json_encode($value);
            }

            $insert->values($data);
            $stmt   = $sql->prepareStatementForSqlObject($insert);
            $result = $stmt->execute();
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function getSleeperGameLogs()
    {
        $week = 1;
        while ($week < 18) {
            $request = new Request();
            $uri = "https://api.sleeper.app/v1/stats/nfl/regular/2017/{$week}";
            $request->setUri($uri);

            $client = new Client();
            $response = $client->send($request);
            $html = $response->getBody();

            $json = (array) json_decode($html);
            print("week {$week} \n");
            $progressBar = new ProgressBar($this->consoleAdapter, 0, count($json));
            $pointer = 0;
            foreach ($json as $key => $value) {
                $data = [];

                $sql = new Sql($this->db);
                $insert = $sql->insert('game_logs');
                $data = [
                    'sleeper_id' => $key,
                    'week' => $week,
                    'year' => '2017'
                ];

                if (!empty((array) $value)) {
                    $data['stats'] = json_encode($value);
                }

                $insert->values($data);
                $stmt   = $sql->prepareStatementForSqlObject($insert);
                $result = $stmt->execute();
                $pointer++;
                $progressBar->update($pointer);
            }
            $progressBar->finish();
            $week++;
        }
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