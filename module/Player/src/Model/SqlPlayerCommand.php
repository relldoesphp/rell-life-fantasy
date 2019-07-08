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
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\ProgressBar\ProgressBar;
use Zend\ProgressBar\Adapter\Console;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Dom\Query;

class SqlPlayerCommand implements PlayerCommandInterface
{

    private $db;
    private $consoleAdapter;
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
        $this->wrCommand = new SqlWrCommand($db, $consoleAdapter);
        $this->rbCommand = new SqlRbCommand($db, $consoleAdapter);
        $this->teCommand = new SqlTeCommand($db, $consoleAdapter);
        $this->olCommand = new SqlOlCommand($db, $consoleAdapter);
        $this->dlCommand = new SqlDlCommand($db, $consoleAdapter);
        $this->olbCommand = new SqlOLBCommand($db, $consoleAdapter);
        $this->ilbCommand = new SqlILBCommand($db, $consoleAdapter);
        $this->cbCommand = new SqlCBCommand($db, $consoleAdapter);
        $this->fsCommand = new SqlFSCommand($db, $consoleAdapter);
        $this->ssCommand = new SqlSSCommand($db, $consoleAdapter);
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

    public function updateSleeperInfo()
    {
        $request = new Request();
        $uri = "https://api.sleeper.app/v1/players/nfl";
        $request->setUri($uri);

        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();

        $json = (array) json_decode($html);

        foreach ($json as $key => $value) {
            $player = [];
            $player['sleeper_id'] = $value->player_id;
            $player['first_name'] = $value->first_name;
            $player['last_name'] = $value->last_name;
            $player['search_full_name'] = $value->first_name." ".$value->last_name;
            $player['position'] = $value->position;
            $player['team'] = $value->team;

            $player['player_info'] = (array)$value;

            unset($player['player_info']['espn_id']);
            unset($player['player_info']['yahoo_id']);
            unset($player['player_info']['rotoworld_id']);
            unset($player['player_info']['rotowire_id']);
            unset($player['player_info']['stats_id']);
            unset($player['player_info']['sportradar_id']);
            unset($player['player_info']['gsis_id']);
            unset($player['player_info']['injury_notes']);
            unset($player['player_info']['injury_body_part']);
            unset($player['player_info']['injury_start_date']);
            unset($player['player_info']['team']);
            unset($player['player_info']['position']);
            unset($player['player_info']['number']);
            unset($player['player_info']['depth_chart_position']);
            unset($player['player_info']['depth_chart_order']);
            unset($player['player_info']['practice_participation']);
            unset($player['player_info']['practice_description']);

            $player['api_info']['espn_id'] = $value->espn_id;
            $player['api_info']['yahoo_id'] = $value->yahoo_id;
            $player['api_info']['rotoworld_id'] = $value->rotoworld_id;
            $player['api_info']['rotowire_id'] = $value->rotowire_id;
            $player['api_info']['stats_id'] = $value->stats_id;
            $player['api_info']['sportradar_id'] = $value->sportradar_id;
            $player['api_info']['gsis_id'] = $value->gsis_id;

            $player['injury_info']['injury_status'] = $value->injury_status;
            $player['injury_info']['injury_notes'] = $value->injury_notes;
            $player['injury_info']['injury_body_part'] = $value->injury_body_part;
            $player['injury_info']['injury_start_date'] = $value->injury_start_date;

            $player['team_info']['team'] = $value->team;
            $player['team_info']['position'] = $value->position;
            $player['team_info']['number'] = $value->number;
            $player['team_info']['depth_chart_position'] = $value->depth_chart_position;
            $player['team_info']['depth_chart_order'] = $value->depth_chart_order;
            $player['team_info']['practice_participation'] = $value->practice_participation;
            $player['team_info']['practice_description'] = $value->practice_description;

            $sql    = new Sql($this->db);
            $select = $sql->select();
            $select->from(['p' => 'player_test']);
            $select->where(['sleeper_id = ?' =>$player['sleeper_id']]);
            $stmt   = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();

            if ($result->count() != 0) {
                /** Update existing player **/
                // Prep Json
                $playerInfoString = $this->makeJsonString($player['player_info']);
                $apiInfoString = $this->makeJsonString($player['api_info']);
                $teamInfoString = $this->makeJsonString($player['team_info']);
                $injuryInfoString = $this->makeJsonString($player['injury_info']);

                // Build Update
                $update = $sql->update('player_test');
                $update->set([
                    'first_name' => $player['first_name'],
                    'last_name' => $player['last_name'],
                    'search_full_name' => $player['search_full_name'],
                    'position' => $player['position'],
                    'team' => $player['team'],
                    'player_info' => new Expression("json_set(player_info, {$playerInfoString})"),
                    'api_info' => new Expression("json_set(api_info, {$apiInfoString})"),
                    'team_info' => new Expression("json_set(team_info, {$teamInfoString})"),
                    'injury_info' => new Expression("json_set(injury_info, {$injuryInfoString})"),
                ]);
                $update->where(['sleeper_id = ?', $player['sleeper_id']]);
            } else {
                /** Insert new player **/
                // Prep Json
                $player['player_info'] = json_encode($player['player_info']);
                $player['api_info'] = json_encode($player['api_info']);
                $player['team_info'] = json_encode($player['team_info']);
                $player['injury_info'] = json_encode($player['injury_info']);

                // Build Insert
                $insert = $sql->insert('player_test');
                $insert->values([
                    'first_name' => $player['first_name'],
                    'last_name' => $player['last_name'],
                    'search_full_name' => $player['search_full_name'],
                    'position' => $player['position'],
                    'team' => $player['team'],
                    'player_info' => $player['player_info'],
                    'api_info' => $player['api_info'],
                    'team_info' => $player['team_info'],
                    'injury_info' => $player['injury_info'],
                ]);
            }
        }
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