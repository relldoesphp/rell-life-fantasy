<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/23/19
 * Time: 10:46 PM
 */

namespace Player\Service;

use Player\Model\Player\Player;
use Player\Model\Player\SqlCommands\SqlQBCommand;
use Player\Model\Player\SqlCommands\SqlWrCommand;
use Player\Model\Player\SqlCommands\SqlRbCommand;
use Player\Model\Player\SqlCommands\SqlTeCommand;
use Player\Model\Player\SqlCommands\SqlOlCommand;
use Player\Model\Player\SqlCommands\SqlDlCommand;
use Player\Model\Player\SqlCommands\SqlOLBCommand;
use Zend\Db\Adapter\AdapterInterface;
use Zend\ProgressBar\Adapter\Console;
use Zend\Http\Request;
use Zend\Http\Client;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;


class PlayerManager
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

    public function __construct(AdapterInterface $db, 
                                Console $consoleAdapter,         
                                PlayerCommandInterface $command,
                                PlayerRepositoryInterface $repository)
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
        $this->repository = $repository;
        $this->command = $command;
        $this->qbCommand = new SqlQBCommand($db, $consoleAdapter);
        $this->wrCommand = new SqlWrCommand($db, $consoleAdapter);
        $this->rbCommand = new SqlRbCommand($db, $consoleAdapter);
        $this->teCommand = new SqlTeCommand($db, $consoleAdapter);
        $this->olCommand = new SqlOlCommand($db, $consoleAdapter);
        $this->dlCommand = new SqlDlCommand($db, $consoleAdapter);
        $this->olbCommand = new SqlOLBCommand($db, $consoleAdapter);
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
            $sleeperId = $value->player_id;
            
            $player = $this->repository->findPlayerBySleeperId($sleeperId);
            if (empty($player)) {
                $player = new Player();
            }
            
            $player->setFirstName($value->first_name);
            $player->setLastName($value->last_name);
            $player->setSearchFullName($value->first_name." ".$value->last_name);
            $player->setPosition($value->position);
            $player->setTeam($value->team);

            $info['player_info'] = (array)$value;

            unset($info['player_info']['espn_id']);
            unset($info['player_info']['yahoo_id']);
            unset($info['player_info']['rotoworld_id']);
            unset($info['player_info']['rotowire_id']);
            unset($info['player_info']['stats_id']);
            unset($info['player_info']['sportradar_id']);
            unset($info['player_info']['gsis_id']);
            unset($info['player_info']['injury_notes']);
            unset($info['player_info']['injury_body_part']);
            unset($info['player_info']['injury_start_date']);
            unset($info['player_info']['team']);
            unset($info['player_info']['position']);
            unset($info['player_info']['number']);
            unset($info['player_info']['depth_chart_position']);
            unset($info['player_info']['depth_chart_order']);
            unset($info['player_info']['practice_participation']);
            unset($info['player_info']['practice_description']);

            $player->setPlayerInfo($info['player_info']);

            $info['api_info']['espn_id'] = $value->espn_id;
            $info['api_info']['yahoo_id'] = $value->yahoo_id;
            $info['api_info']['rotoworld_id'] = $value->rotoworld_id;
            $info['api_info']['rotowire_id'] = $value->rotowire_id;
            $info['api_info']['stats_id'] = $value->stats_id;
            $info['api_info']['sportradar_id'] = $value->sportradar_id;
            $info['api_info']['gsis_id'] = $value->gsis_id;

            $player->setApiInfo($info['api_info']);

            $info['injury_info']['injury_status'] = $value->injury_status;
            $info['injury_info']['injury_notes'] = $value->injury_notes;
            $info['injury_info']['injury_body_part'] = $value->injury_body_part;
            $info['injury_info']['injury_start_date'] = $value->injury_start_date;

            $player->setInjuryInfo($info['injury_info']);

            $info['team_info']['team'] = $value->team;
            $info['team_info']['position'] = $value->position;
            $info['team_info']['number'] = $value->number;
            $info['team_info']['depth_chart_position'] = $value->depth_chart_position;
            $info['team_info']['depth_chart_order'] = $value->depth_chart_order;
            $info['team_info']['practice_participation'] = $value->practice_participation;
            $info['team_info']['practice_description'] = $value->practice_description;

            $player->setTeamInfo($info['team_info']);

            $this->command->save($player);
        }
    }





}