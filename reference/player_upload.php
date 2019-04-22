<?php

$servername = "localhost";
$username = "rell";
$password = "rell";

//try {
//    $conn = new PDO("mysql:host=$servername;dbname=fantasy_football", $username, $password);
//    // set the PDO error mode to exception
//    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//
//    $csv = array_map('str_getcsv', file('/Users/tcook/Sites/rell/rell-life-fantasy/data/te_big.csv'));
//
//    foreach ($csv as $data) {
//        if ($data[0] !== "Full Name") {
//            $player = [];
//            $name = explode(" ", $data[0]);
//            $player['firstName'] = $name[0];
//            $player['lastName'] = $name[1];
//            $alias = str_replace(" ", "-", $data[0]);
//            $player['alias'] = str_replace(".", "", $alias);
//            $player['team'] = $data[11];
//            $player['height'] = $data[10];
//            $player['heightInches'] = $data[4];
//            $player['bmi'] = $data[2];
//            $player['arms'] = $data[9];
//            $player['weight'] = str_replace(" lbs", "", $data[5]);
//
//            $arms = explode(" ", $data[9]);
//            $armFraction = explode("/", $arms[1]);
//            $player['armsInches'] = $arms[0] + ($armFraction[0]/$armFraction[1]);
//
//            $hands = explode(" ", $data[8]);
//            $handsFraction = explode("/", $hands[1]);
//            $player['hands'] = $hands[0] + $handsFraction[0]/$handsFraction[1];
//            $player['age'] = $data[6];
//            $player['birthDate'] = $data[7];
//            $player['draftPick'] = $data[3];
//            $player['position'] = $data[1];
//
//            foreach($player as $k => $v) {
//                if ($v == "-") {
//                   $player[$k] = null;
//                }
//            }
//
//            $sql = <<<EOT
//INSERT into players (firstName, lastName, alias, team, height, heightInches, bmi, arms, armsInches, hands, age, birthDate, draftPick, position, weight)
//values (:firstName,:lastName,:alias,:team,:height,:heightInches,:bmi,:arms,:armsInches,:hands,:age,:birthDate,:draftPick,:position, :weight)
//EOT;
//
//
//            $stmt= $conn->prepare($sql);
//            $stmt->execute($player);
//
//            print "{$data[0]} updated.\n";
//        }
//    }
//
//} catch(PDOException $e) {
//    echo "Connection failed: " . $e->getMessage();
//}

try {
    $conn = new PDO("mysql:host=$servername;dbname=fantasy_football", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $csv = array_map('str_getcsv', file('/Users/tcook/Sites/rell/rell-life-fantasy/data/rb_big.csv'));

    foreach ($csv as $data) {
        if ($data[0] !== "Full Name") {
            $player = [];
            $name = explode(" ", $data[0]);
            $alias = str_replace(" ", "-", $data[0]);
            $player['alias'] = str_replace(".", "", $alias);
            $player['college'] = $data[6];

            $sql = <<<EOT
Update players SET college = :college WHERE alias = :alias;
EOT;

            $stmt= $conn->prepare($sql);
            $stmt->execute($player);

            print "{$data[0]} updated.\n";
        }
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=fantasy_football", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $csv = array_map('str_getcsv', file('/Users/tcook/Sites/rell/rell-life-fantasy/data/te_metrics.csv'));

    foreach ($csv as $data) {
        if ($data[0] !== "Full Name") {
            $player = [];
            $name = explode(" ", $data[0]);
            $alias = str_replace(" ", "-", $data[0]);
            $player['alias'] = str_replace(".", "", $alias);

            $sql = <<<EOT
Select id, team  
from players
where alias = :alias
EOT;

            $stmt = $conn->prepare($sql);
            $stmt->execute(array(':alias' => $player['alias']));

            $result = $stmt->fetch();

            $metrics = [];
            $metrics['playerId'] = $result['id'];
            $metrics['team'] = $result['team'];
            $metrics['fortyTime'] = $data[5];
            $metrics['broadJump'] = $data[6];
            $metrics['verticalJump'] = $data[7];
            $metrics['benchPress'] = $data[2];
            $metrics['cone'] = $data[3];
            $metrics['shuttle'] = $data[4];
            $metrics['breakoutAge'] = $data[10];
            $metrics['collegeDominator'] = str_replace("%","", $data[9]);
            $metrics['collegeYPR'] = $data[8];

            foreach($metrics as $k => $v) {
                if ($v == "-") {
                   $metrics[$k] = null;
                }
            }

            $sql = <<<EOT
INSERT into te_metrics (playerId, team, fortyTime, verticalJump, broadJump, benchPress, shuttle, cone, breakoutAge, collegeDominator,collegeYPR)
values (:playerId, :team, :fortyTime, :verticalJump, :broadJump, :benchPress, :shuttle, :cone, :breakoutAge, :collegeDominator, :collegeYPR)
EOT;

            $stmt= $conn->prepare($sql);
            $stmt->execute($metrics);

            print "{$data[0]} updated.\n";

        }
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}