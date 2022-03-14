<?php
$jsonFile = file_get_contents('projections.json');
$json = json_decode($jsonFile, true);

function makeFantasyPoints($player) {
    $statValues = [
        'pass_att' => [
            'name' => 'PassingAttempts',
            'value' => 0,
        ],
        'pass_cmp' => [
            'name' => 'PassingCompletions',
            'value' => 0,
        ],
        'pass_yds' => [
            'name' => 'PassingYards',
            'value' => 0.04,
        ],
        'pass_tds' => [
            'name' => 'PassingTouchdowns',
            'value' => 4,
        ],
        'rus_att' => [
            'name' => 'RushingAttempts',
            'value' => 0,
        ],
        'rus_yds' => [
            'name' => 'RushingYards',
            'value' => .1,
        ],
        'rus_tds' => [
            'name' => 'RushingTouchdowns',
            'value' => 6,
        ],
        'rec_rec' => [
            'name' => 'Receptions',
            'value' => 1,
        ],
        'rec_yds' => [
            'name' => 'ReceivingYards',
            'value' => .1,
        ],
        'rec_tds' => [
            'name' => 'ReceivingTouchdowns',
            'value' => 6,
        ],
    ];
    $projections = [];
    $points = 0;
    foreach ($player as $key => $value) {
        if (array_key_exists($key, $statValues)) {
            $points = $points + ($value * $statValues[$key]['value']);
            $projections[$statValues[$key]['name']] = $value;
        }
    }
    $projections['FantasyPoints'] = round($points,2);
    return $projections;
}

function comparePoints($a,$b) {
    return $a['Projections']['FantasyPoints'] < $b['Projections']['FantasyPoints'];
}

$events = [];
foreach ($json as $projection) {
    // If game is not in events array add it
    if (!array_key_exists($projection['GameID'], $events)) {
        $events[$projection['GameID']] = [
            'GameID' => $projection['GameID'],
            'DateTime' => $projection['DateTime'],
            'Teams' => []
        ];
    }

    // If team isn't in the game details add it
    $teams = $events[$projection['GameID']]['Teams'];
    if (!array_key_exists($projection['TeamID'], $teams)) {
        $teams[$projection['TeamID']] = [
            'TeamID' => $projection['TeamID'],
            'Team' => $projection['Team'],
            'Team_Abbr' => $projection['Team_Abbr'],
            'Players' => [],
        ];
        $events[$projection['GameID']]['Teams'] = $teams;
    }

    $players = $events[$projection['GameID']]['Teams'][$projection['TeamID']]['Players'];
    if (!array_key_exists($projection['PlayerID'], $players)) {
        $players[$projection['PlayerID']] = [
            'PlayerID' => $projection['PlayerID'],
            'Player' => $projection['Player'],
            'Position' => $projection['Position'],
            'Projections' => makeFantasyPoints($projection)
        ];
        $events[$projection['GameID']]['Teams'][$projection['TeamID']]['Players'] = $players;
    }
}

foreach ($events as $gameID =>  $game) {
    foreach ($game['Teams'] as $teamID => $team) {

        $events[$gameID]['Teams'][$teamID]['Players'] = $team['Players'];
    }
}

$updatedProjections = [
    'Events' => $events
];

file_put_contents('updatedProjections.json', json_encode($updatedProjections));
