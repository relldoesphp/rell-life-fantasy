<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 5/2/19
 * Time: 11:10 PM
 */

$servername = "localhost";
$username = "rell";
$password = "rell";

try {
    $conn = new PDO("mysql:host=$servername;dbname=fantasy_football", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $json = json_decode(file_get_contents("/Users/tcook/Sites/rell/rell-life-fantasy/data/nfl"));

    foreach ($json as $key => $value) {
        $player = [];
        $player['firstName'] = $value['first_name'];
        $player['lastName'] = $value['last_name'];
        $alias = str_replace(" ", "-", $data[0]);
        $player['alias'] = str_replace(".", "", $alias);
        $player['team'] = $data[11];
        $player['height'] = $data[10];
        $player['heightInches'] = $data[4];
        $player['bmi'] = $data[2];
        $player['arms'] = $data[9];
        $player['weight'] = str_replace(" lbs", "", $data[5]);

        $arms = explode(" ", $data[9]);
        $armFraction = explode("/", $arms[1]);
        $player['armsInches'] = $arms[0] + ($armFraction[0]/$armFraction[1]);

        $hands = explode(" ", $data[8]);
        $handsFraction = explode("/", $hands[1]);
        $player['hands'] = $hands[0] + $handsFraction[0]/$handsFraction[1];
        $player['age'] = $data[6];
        $player['birthDate'] = $data[7];
        $player['draftPick'] = $data[3];
        $player['position'] = $data[1];

        foreach($player as $k => $v) {
            if ($v == "-") {
               $player[$k] = null;
            }
        }

        $sql = <<<EOT
INSERT into players (firstName, lastName, alias, team, height, heightInches, bmi, arms, armsInches, hands, age, birthDate, draftPick, position, weight)
values (:firstName,:lastName,:alias,:team,:height,:heightInches,:bmi,:arms,:armsInches,:hands,:age,:birthDate,:draftPick,:position, :weight)
EOT;


        $stmt= $conn->prepare($sql);
        $stmt->execute($player);

        print "{$data[0]} updated.\n";
        }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
