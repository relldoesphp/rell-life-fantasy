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
        $uri = "https://api.sleeper.app/v1/stats/nfl/regular/2017";
        $request->setUri($uri);

        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();

        $json = (array) json_decode($html);
        foreach ($json as $key => $value) {
            $data = [];

            $sql = new Sql($this->db);
            $insert = $sql->insert('season_stats');
            $data = [
                'sleeperId' => $key,
                'year' => '2017'
            ];

            if (!empty((array) $value)) {
                $data['stats'] = json_encode($value);
            }

            $insert->values($data);
            $stmt   = $sql->prepareStatementForSqlObject($insert);
            $result = $stmt->execute();
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
}