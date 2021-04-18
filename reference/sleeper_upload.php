<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 5/2/19
 * Time: 11:10 PM
 */

$servername = "drafttradewin.com";
$username = "rell";
$password = "3523Kaleb!";

try {
    $conn = new PDO("mysql:host=$servername;dbname=dtw_dev", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $json = json_decode(file_get_contents("/Users/tcook/Sites/rell/rell-life-fantasy/data/nfl"));

    foreach ($json as $key => $value) {
        $player = [];
        $player['sleeper_id'] = $value->player_id;
        $player['first_name'] = $value->first_name;
        $player['last_name'] = $value->last_name;
        $player['search_full_name'] = $value->search_full_name;
        $player['position'] = $value->position;
        $player['team'] = $value->team;

        $player['player_info'] = (array) $value;

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

        $player['api_info']['espn_id']  = $value->espn_id;
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

        $player['player_info'] = json_encode($player['player_info']);
        $player['api_info'] = json_encode($player['api_info']);
        $player['team_info'] = json_encode($player['team_info']);
        $player['injury_info'] = json_encode($player['injury_info']);

        $sql = <<<EOT
INSERT into player_test (
  first_name, 
  last_name, 
  search_full_name, 
  sleeper_id,
  team,
  position,
  player_info,
  injury_info,
  team_info,
  api_info
) values (
  :first_name,
  :last_name,
  :search_full_name, 
  :sleeper_id, 
  :team, 
  :position, 
  :player_info, 
  :injury_info,
  :team_info, 
  :api_info
)
EOT;
        $stmt= $conn->prepare($sql);
        $stmt->execute($player);

        print "{$player['search_full_name']} updated.\n";
        }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
