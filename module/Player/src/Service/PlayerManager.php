<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/23/19
 * Time: 10:46 PM
 */

namespace Player\Service;

use Player\Model\Player\Player;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Json\Json;
use Zend\ProgressBar\Adapter\Console;
use Zend\Http\Request;
use Zend\Http\Client;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Zend\ProgressBar\ProgressBar;
use Player\Service\Position;


class PlayerManager
{
    private $db;
    private $consoleAdapter;
    private $command;
    private $repository;

    public function __construct(AdapterInterface $db, 
                                Console $consoleAdapter,         
                                PlayerCommandInterface $command,
                                PlayerRepositoryInterface $repository)
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
        $this->repository = $repository;
        $this->command = $command;
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

        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($json));
        $pointer = 0;
        foreach ($json as $key => $value) {
            $sleeperId = $value->player_id;
            
            $player = $this->repository->findPlayerBySleeperId($sleeperId);
            if (empty($player)) {
                $player = new Player();
            }
            
            $player->setFirstName($value->first_name);
            $player->setLastName($value->last_name);
            $player->setSearchFullName($value->search_full_name);
            $player->setPosition($value->position);
            $player->setTeam($value->team);

            $playerInfo = Json::decode($player->getPlayerInfo(),1);
            $playerInfo['age'] = $value->age;
            $playerInfo['sport'] = $value->sport;
            $playerInfo['height'] = $value->height;
            $playerInfo['status'] = $value->status;
            $playerInfo['weight'] = $value->weight;
            $playerInfo['college'] = $value->college;
            $playerInfo['hashtag'] = $value->hashtag;
            $playerInfo['player_id'] = $value->player_id;
            $playerInfo['years_exp'] = $value->years_exp;
            $playerInfo['high_school'] = $value->birth_city;
            $playerInfo['birth_city'] = $value->birth_city;
            $playerInfo['birth_state'] = $value->birth_state;
            $playerInfo['birth_country'] = $value->birth_country;
            $playerInfo['birth_date'] = $value->birth_date;
            $playerInfo['first_name'] = $value->first_name;
            $playerInfo['last_name'] = $value->last_name;
            $playerInfo['search_rank'] = $value->search_rank;
            $playerInfo['news_updated'] = $value->news_updated;
            $playerInfo['injury_status'] = $value->injury_status;
            $playerInfo['search_full_name'] = $value->search_full_name;
            $playerInfo['search_first_name'] = $value->search_first_name;
            $playerInfo['search_last_name'] = $value->search_last_name;
            $player->setPlayerInfo($playerInfo);

            $apiInfo = Json::decode($player->getApiInfo(),1);
            $apiInfo['espn_id'] = $value->espn_id;
            $apiInfo['yahoo_id'] = $value->yahoo_id;
            $apiInfo['rotoworld_id'] = $value->rotoworld_id;
            $apiInfo['rotowire_id'] = $value->rotowire_id;
            $apiInfo['stats_id'] = $value->stats_id;
            $apiInfo['sportradar_id'] = $value->sportradar_id;
            $apiInfo['gsis_id'] = $value->gsis_id;
            $player->setApiInfo($apiInfo);

            $injuryInfo = Json::decode($player->getInjuryInfo(),1);
            $injuryInfo['injury_status'] = $value->injury_status;
            $injuryInfo['injury_notes'] = $value->injury_notes;
            $injuryInfo['injury_body_part'] = $value->injury_body_part;
            $injuryInfo['injury_start_date'] = $value->injury_start_date;
            $player->setInjuryInfo( $injuryInfo);

            $teamInfo = Json::decode($player->getTeamInfo(),1);
            $teamInfo['team'] = $value->team;
            $teamInfo['position'] = $value->position;
            $teamInfo['number'] = $value->number;
            $teamInfo['depth_chart_position'] = $value->depth_chart_position;
            $teamInfo['depth_chart_order'] = $value->depth_chart_order;
            $teamInfo['practice_participation'] = $value->practice_participation;
            $teamInfo['practice_description'] = $value->practice_description;
            $player->setTeamInfo($teamInfo);

            $this->command->save($player);
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function updateWrMetrics()
    {
        $wrService = new Position\WrService($this->db, $this->consoleAdapter, $this->command, $this->repository);
        $wrService->calculateMetrics("WR");
        $wrService->calculatePercentiles("WR");
        $wrService->calculateSpecialScores();
        $wrService->calculateSpecialPercentiles();

        $rbService = new Position\RbService($this->db, $this->consoleAdapter, $this->command, $this->repository);
        $rbService->calculateMetrics("RB");
        $rbService->calculatePercentiles("RB");
        $rbService->calculateSpecialScores();
        $rbService->calculateSpecialPercentiles();

        $teService = new Position\TeService($this->db, $this->consoleAdapter, $this->command, $this->repository);
        $teService->calculateMetrics("TE");
        $teService->calculatePercentiles("TE");
        $teService->calculateSpecialScores();
        $teService->calculateSpecialPercentiles();

    }
}