<?php

$servername = "drafttradewin.com";
$username = "rell";
$password = "3523Kaleb!";

try {
    $conn = new PDO("mysql:host=$servername;dbname=dtw_dev", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $csv = array_map('str_getcsv', file('/Users/tcook/Sites/rell/rell-life-fantasy/data/qb_player.csv'));
    /** RB csv
     * 0 = Name
     * 1 = Position
     * 2 = Draft Year
     * 3 = Weight
     * 4 = Height Inches
     * 5 = Draft Pick
     * 6 = popularity
     * 7 = Age
     * 8 = Arm Length
     * 9 = Height
     * 10 = Hand Size
     * 11 = College
     */

    foreach ($csv as $data) {
        if ($data[0] !== "Full Name") {
            $player = [];
            $searchName = str_replace(' ', '', $data[0]); // Replaces all spaces with hyphens.
            $searchName = str_replace('.', '', $searchName);
            $player['searchName'] = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $searchName));


            $sql = <<<EOT
Select id, first_name, last_name, sleeper_id
from player_test
WHERE search_full_name = :searchName AND position in ('RB');
EOT;
            $stmt= $conn->prepare($sql);
            $stmt->execute($player);
            $row = $stmt->fetchObject();
            if (!empty($row->id)) {
                $player = [];
                $player['id'] = $row->id;
                $player['draft_pick'] = $data[5];
                $player['draft_year'] = $data[2];
                $player['arms'] = $data[8];
                $player['heightInches'] = $data[4];

                $arms = explode(" ", $data[8]);
                if (array_key_exists(1, $arms)) {
                    $armFraction = explode("/", $arms[1]);
                    if (is_array($armFraction) && array_key_exists(1, $armFraction) && $armFraction[1] > 0) {
                        $player['armsInches'] = $arms[0] + ($armFraction[0]/$armFraction[1]);
                    } else {
                        $player['armsInches'] = $arms[0];
                    }
                } else {
                    $player['armsInches'] = $arms[0];
                }

                $hands = explode(" ", $data[10]);
                if (array_key_exists(1, $hands)) {
                    $handsFraction = explode("/", $hands[1]);
                    if (is_array($handsFraction) && array_key_exists(1, $handsFraction) && $handsFraction[1] > 0) {
                        $player['hands'] = $hands[0] + $handsFraction[0]/$handsFraction[1];
                    } else {
                        $player['hands'] = $hands[0];
                    }
                } else {
                    $player['hands'] = $hands[0];
                }

                $sql = <<<EOT
Update player_test 
SET player_info = json_set(player_info, '$.draft_pick', :draft_pick, '$.draft_year', :draft_pick, '$.arms', :arms,'$.armsInches', :armsInches,'$.heightInches', :heightInches, '$.hands', :hands)
WHERE id = :id;
EOT;
                $stmt= $conn->prepare($sql);
                $stmt->execute($player);
                print "{$data[0]} updated.\n";
            }
        }
    }

    $csv = array_map('str_getcsv', file('/Users/tcook/Sites/rell/rell-life-fantasy/data/qb_metrics.csv'));
    /** WR csv
     * 0 = Name
     * 1 = Position
     * 2 = Draft Year
     * 3 = Weight
     * 4 = Height Inches
     * 5 = Draft Pick
     * 6 = popularity
     * 7 = Age
     * 8 = Arm Length
     * 9 = Height
     * 10 = Hand Size
     * 11 = College
     */
    foreach ($csv as $data) {
        if ($data[0] !== "Full Name") {
            $player = [];
            $searchName = str_replace('.', '', $data[0]);
            $searchName = str_replace(' ', '', $searchName); // Replaces all spaces with hyphens.
            $player['searchName'] = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', $searchName));

            $sql = <<<EOT
Select id, first_name, last_name, sleeper_id, position
from player_test
WHERE search_full_name = :searchName AND position in ('QB');
EOT;
            $stmt= $conn->prepare($sql);
            $stmt->execute($player);
            $row = $stmt->fetchObject();
            if (!empty($row->id)) {
                $metrics = [];
                $metrics['id'] = $row->id;
                $metrics['shuttle'] = $data[2];
                $metrics['cone'] = $data[3];
                $metrics['fortyTime'] = $data[6];
                $metrics['broadJump'] = $data[4];
                $metrics['verticalJump'] = $data[7];
                $metrics['throwVelocity'] = $data[5];
                $metrics['wonderlic'] = $data[8];

                foreach ($metrics as $key => $metric) {
                    if ($metric == '-') {
                        $metrics[$key] = "";
                    }
                }

                $sql = <<<EOT
Update player_test 
SET metrics = json_set('{}', '$.shuttle', :shuttle, '$.cone', :cone, '$.fortyTime', :fortyTime, '$.broadJump', :broadJump,'$.verticalJump', :verticalJump,'$.throwVelocity', :throwVelocity, '$.wonderlic', :wonderlic)
WHERE id = :id;
EOT;

                $stmt= $conn->prepare($sql);
                $stmt->execute($metrics);
                print "{$data[0]} updated.\n";
            }
        }
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
