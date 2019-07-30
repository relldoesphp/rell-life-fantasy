<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 3:40 PM
 */

namespace Player\Model\Player;

use Zend\Json\Json;

class Player
{
    /**
     * @var
     */
    private $id = 0;
    private $first_name = "";
    private $last_name = "";
    private $search_full_name;
    private $team = "";
    private $position = "";
    private $player_info = '{}';
    private $team_info = '{}';
    private $api_info = '{}';
    private $injury_info = "{}";
    private $metrics = "{}";
    private $percentiles = "{}";
    private $college_stats = "{}";
    private $sleeper_id = "";
    public $collegeTable = "";
    public $ordinals = "";
    public $seasonStats = "";
    public $gameLogs = "";
    public $images = "{}";

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $first_name
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * @param string $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    /**
     * @param mixed $search_full_name
     */
    public function setSearchFullName($search_full_name)
    {
        $this->search_full_name = $search_full_name;
    }

    /**
     * @param string $team
     */
    public function setTeam($team)
    {
        $this->team = $team;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param array $player_info
     */
    public function setPlayerInfo($player_info)
    {
        $this->player_info = $player_info;
    }

    /**
     * @param array $team_info
     */
    public function setTeamInfo($team_info)
    {
        $this->team_info = $team_info;
    }

    /**
     * @param array $api_info
     */
    public function setApiInfo($api_info)
    {
        $this->api_info = $api_info;
    }

    /**
     * @param array $injury_info
     */
    public function setInjuryInfo($injury_info)
    {
        $this->injury_info = $injury_info;
    }

    /**
     * @param array $metrics
     */
    public function setMetrics($metrics)
    {
        $this->metrics = $metrics;
    }

    /**
     * @param string $percentiles
     */
    public function setPercentiles($percentiles)
    {
        $this->percentiles = $percentiles;
    }

    /**
     * @param string $college_stats
     */
    public function setCollegeStats($college_stats)
    {
        $this->college_stats = $college_stats;
    }

    /**
     * @param string $sleeper_id
     */
    public function setSleeperId($sleeper_id)
    {
        $this->sleeper_id = $sleeper_id;
    }

    /**
     * @param string $collegeTable
     */
    public function setCollegeTable($collegeTable)
    {
        $this->collegeTable = $collegeTable;
    }

    /**
     * @param string $ordinals
     */
    public function setOrdinals($ordinals)
    {
        $this->ordinals = $ordinals;
    }

    /**
     * @param string $seasonStats
     */
    public function setSeasonStats($seasonStats)
    {
        $this->seasonStats = $seasonStats;
    }

    /**
     * @param string $gameLogs
     */
    public function setGameLogs($gameLogs)
    {
        $this->gameLogs = $gameLogs;
    }

    /**
     * @param string $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    public function getSearchFullName()
    {
        return $this->search_full_name;
    }
    /**
     * @return mixed
     */
    public function getTeam()
    {
        return $this->team;
    }

    public function getSleeperId()
    {
        return $this->sleeper_id;
    }


    public function decodeJson()
    {
        $this->player_info = (!empty($this->player_info)) ? Json::decode($this->player_info, 1) : ['draft_pick' => ''];
        $this->team_info = (!empty($this->team_info)) ? Json::decode($this->team_info, 1) : [];
        $this->api_info = (!empty($this->api_info)) ? Json::decode($this->api_info, 1) : [];
        $this->injury_info = (!empty($this->injury_info)) ? Json::decode($this->injury_info, 1) : [];
        $this->metrics = (!empty($this->metrics)) ? Json::decode($this->metrics, 1) : [];
        $this->percentiles = (!empty($this->percentiles)) ? Json::decode($this->percentiles, 1) : [];
        $this->college_stats = (!empty($this->college_stats)) ? Json::decode($this->college_stats, 1) : [];
        $this->images = (!empty($this->images)) ? Json::decode($this->images, 1) : [];
    }

//    public function encodeJson()
//    {
//        $this->player_info = (is_array($this->player_info)) ? Json::encode($this->player_info) : [];
//        $this->team_info = (!empty($this->team_info)) ? Json::encode($this->team_info) : [];
//        $this->api_info = (!empty($this->api_info)) ? Json::decode($this->api_info, 1) : [];
//        $this->injury_info = (!empty($this->injury_info)) ? Json::decode($this->injury_info, 1) : [];
//        $this->metrics = (!empty($this->metrics)) ? Json::decode($this->metrics, 1) : [];
//        $this->percentiles = (!empty($this->percentiles)) ? Json::decode($this->percentiles, 1) : [];
//        $this->college_stats = (!empty($this->college_stats)) ? Json::decode($this->college_stats, 1) : [];
//        $this->images = (!empty($this->images)) ? Json::decode($this->images, 1) : [];
//    }

    public function makeSeasonStats()
    {
        $tableData = [];
        if (!empty($this->seasonStats['0']['stats'])) {
            $seasonStats = $this->seasonStats;
            foreach ($seasonStats as $seasonStat) {
                if ($seasonStat['stats'] == null) {
                    continue;
                }
                $stats = Json::decode($seasonStat['stats'], 1);
                if (($this->position == "QB" ||$this->position == "WR" || $this->position == "TE" || $this->position == "RB") && array_key_exists('gp', $stats)) {
                    $tableData[] = [
                        "year" => $seasonStat['year'],
                        "stats" => $stats
                    ];
                }
            }
        }

        return $tableData;
    }

    public function makeGameLogs()
    {
        $tableData = [];
        if (!empty($this->gameLogs['0']['stats'])) {
            $gameLogs = $this->gameLogs;
            foreach ($gameLogs as $gameLog) {
                if ($gameLog['stats'] == null || empty($gameLog['stats'])) {
                    continue;
                }
                $stats = Json::decode($gameLog['stats'], 1);
                if (!array_key_exists('off_snp', $stats)) {
                    continue;
                }
                if ($this->position == "QB" || $this->position == "WR" || $this->position == "TE" || $this->position == "RB") {
                    $tableData[] = [
                        'year' => $gameLog['year'],
                        'week' => $gameLog['week'],
                        'stats' => $stats,
                    ];
                }
            }
        }
        return $tableData;
    }

    public function makeCollegeTable()
    {
        $tableData = [];
        if (!empty($this->college_stats)) {
            $collegeStats = (array) $this->college_stats;
            foreach ($collegeStats as $year => $stats) {
                if ($this->position == "QB") {
                    $tableData[] = [
                        "year" => $year,
                        "stats" => $stats
                    ];
                }
                if ($this->position == "WR") {
                    $tableData[] = [
                        "year" => $year,
                        "college" => $stats['college'],
                        "class" => $stats['class'],
                        "games" => $stats['games'],
                        "recs" => $stats['recs'],
                        "recYds" => $stats['recYds'],
                        "recTds" => $stats['recTds'],
                        "recAvg" => $stats['recAvg'],
                        "recDom" => round($stats['recs']/$stats['totals']['recs'] * 100,1)."%",
                        "ydsDom" => round($stats['recYds']/$stats['totals']['yds'] * 100, 1)."%",
                        "tdsDom" => round($stats['recTds']/$stats['totals']['tds'] * 100,1)."%",
                    ];
                }

                if ($this->position == "RB") {
                    $tableData[] = [
                        "year" => $year,
                        "college" => $stats['college'],
                        "class" => $stats['class'],
                        "games" => $stats['games'],
                        "rushAtt" => $stats['rushAtt'],
                        "rushYds" => $stats['rushYds'],
                        "rushAvg" => $stats['rushAvg'],
                        "rushTds" => $stats['rushTds'],
                        "recs" => $stats['recs'],
                        "recYds" => $stats['recYds'],
                        "recAvg" => $stats['recAvg'],
                        "recTds" => $stats['recTds'],
                        "carryDom" => round(($stats['rushAtt'] / $stats['totals']['carries']) * 100, 1)."%",
                        "recDom" => round($stats['recDominator'],1)."%",
                        "ydDom" => round($stats['ydsDominator'],1)."%",
                        "tdDom" => round($stats['tdDominator'],1)."%"
                    ];
                }

                if ($this->position == "TE") {
                    $tableData[] = [
                        "year" => $year,
                        "college" => $stats['college'],
                        "class" => $stats['class'],
                        "games" => $stats['games'],
                        "recs" => $stats['recs'],
                        "recYds" => $stats['recYds'],
                        "recTds" => $stats['recTds'],
                        "recAvg" => $stats['recAvg'],
                        "recDom" => round($stats['recs']/$stats['totals']['recs'] * 100,1)."%",
                        "ydsDom" => round($stats['recYds']/$stats['totals']['yds'] * 100, 1)."%",
                        "tdsDom" => round($stats['recTds']/$stats['totals']['tds'] * 100,1)."%",
                    ];
                }
            }
        }
        return $tableData;
    }

    public function getAllInfo()
    {
        $this->decodeJson();
        $this->ordinals = $this->makeOrdinals();
        $this->collegeTable = $this->makeCollegeTable();
        $this->seasonTable = $this->makeSeasonStats();
        $this->gameLogTable = $this->makeGameLogs();
        $this->fillEmptyValues();
        $this->makeImages();
        return get_object_vars($this);
    }

    private function makeOrdinals()
    {
        $ordinals = [];
        foreach($this->percentiles as $key => $value) {
            $nf = new \NumberFormatter('en_US', \NumberFormatter::ORDINAL);
            $ordinals[$key] = $nf->format($value);
        }
        return $ordinals;
    }

    private function fillEmptyValues()
    {
        if (!array_key_exists('arms', $this->player_info)) {
            $this->player_info['arms'] = '';
        }

        if (!array_key_exists('bmi', $this->player_info)) {
            $this->player_info['bmi'] = '';
        }

        if (!array_key_exists('draft_pick', $this->player_info)) {
            $this->player_info['draft_pick'] = '';
        }

        if (!array_key_exists('draft_year', $this->player_info)) {
            $this->player_info['draft_year'] = '';
        }
    }

    private function makeImages()
    {
        $images = "";
        if (empty($this->images)) {
            $images = "<a class='carousel-item' href='#one!'><img src='https://sleepercdn.com/content/nfl/players/{$this->sleeper_id}.jpg'  style='height:280px; width:75%; margin:auto;'></a>";
        } else {
            foreach ($this->images as $key => $url) {
                $images .= "<a class='carousel-item' href='#{$key}!'><img src='{$url}' style='height:280px'></a>\n";
            }
        }
        $this->images = $images;
    }

    public function getArrayCopy()
    {
        return [
            'id'     => $this->id,
            'first_name'  => $this->first_name,
            'last_name'  => $this->last_name,
            'team' => $this->team,
            'position' => $this->position,
            'player_info' => $this->player_info,
            'team_info' => $this->team_info,
            'api_info' => $this->api_info,
            'injury_info' => $this->injury_info,
            'metrics' => $this->metrics,
            'percentiles' => $this->percentiles,
            'college_stats' => $this->college_stats,
            'images' => $this->images
        ];
    }
}