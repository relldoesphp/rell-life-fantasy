<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 3:40 PM
 */

namespace Player\Model\Player;

use Zend\Json\Json;
use DomainException;
use Zend\Filter\StringTrim;
use Zend\Filter\StripTags;
use Zend\Filter\ToInt;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator\StringLength;

class Player implements InputFilterAwareInterface
{
    /**
     * @var
     */
    private $id = 0;
    public $first_name = "";
    public $last_name = "";
    public $search_full_name;
    public $team = "";
    public $position = "";
    public $player_info = '{}';
    public $team_info = '{}';
    public $api_info = '{}';
    public $injury_info = "{}";
    public $metrics = "{}";
    public $percentiles = "{}";
    public $college_stats = "{}";
    public $sleeper_id = "";
    public $image1 = "";
    public $image2 = "";
    public $image3 = "";
    public $image4= "";
    public $status="";

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getPlayerInfo()
    {
        return $this->player_info;
    }

    /**
     * @return string
     */
    public function getTeamInfo()
    {
        return $this->team_info;
    }

    /**
     * @return string
     */
    public function getApiInfo()
    {
        return $this->api_info;
    }

    /**
     * @return string
     */
    public function getInjuryInfo()
    {
        return $this->injury_info;
    }

    /**
     * @return string
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * @return string
     */
    public function getPercentiles()
    {
        return $this->percentiles;
    }

    /**
     * @return string
     */
    public function getCollegeStats()
    {
        return $this->college_stats;
    }

    /**
     * @return string
     */
    public function getImages()
    {
        return $this->images;
    }
    public $collegeTable = "";
    public $ordinals = "";
    public $seasonStats = "";
    public $gameLogs = "";
    public $images = "{}";

    private $inputFilter;

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
        if (!is_array($this->player_info)) {
            $this->player_info = (!empty($this->player_info)) ? Json::decode($this->player_info, 1) : ['draft_pick' => ''];
        }

        if (!is_array($this->api_info)) {
            $this->api_info = (!empty($this->api_info)) ? Json::decode($this->api_info, 1) : [];
        }

        if (!is_array($this->team_info)) {
            $this->team_info = (!empty($this->team_info)) ? Json::decode($this->team_info, 1) : [];
        }

        if (!is_array($this->injury_info)) {
            $this->injury_info = (!empty($this->injury_info)) ? Json::decode($this->injury_info, 1) : [];
        }

        if (!is_array($this->metrics)) {
            $this->metrics = (!empty($this->metrics)) ? Json::decode($this->metrics, 1) : [];
        }

        if (!is_array($this->percentiles)) {
            $this->percentiles = (!empty($this->percentiles)) ? Json::decode($this->percentiles, 1) : [];
        }

        if (!is_array($this->college_stats)) {
            $this->college_stats = (!empty($this->college_stats)) ? Json::decode($this->college_stats, 1) : [];
        }

        if (!is_array($this->images)) {
            $this->images = (!empty($this->images)) ? Json::decode($this->images, 1) : [];
        }
    }

    public function encodeJson()
    {
        if (is_array($this->player_info)) {
            $this->player_info = Json::encode($this->player_info);
        }

        if (is_array($this->api_info)) {
            $this->api_info = Json::encode($this->api_info);
        }

        if (is_array($this->team_info)) {
            $this->team_info = Json::encode($this->team_info);
        }

        if (is_array($this->injury_info)) {
            $this->injury_info = Json::encode($this->injury_info);
        }

        if (is_array($this->metrics)) {
            $this->metrics = Json::encode($this->metrics);
        }

        if (is_array($this->percentiles)) {
            $this->percentiles = Json::encode($this->percentiles);
        }

        if (is_array($this->college_stats)) {
            $this->college_stats = Json::encode($this->college_stats);
        }

        if (is_array($this->images)) {
            $this->images = Json::encode($this->images);
        }
    }

    public function makeSeasonStats()
    {
        $tableData = [];
        if (!empty($this->seasonStats)) {
            $seasonStats = $this->seasonStats;
            foreach ($seasonStats as $year => $seasonStat) {
                if (empty($seasonStat['stats'])) {
                    continue;
                }

                if ($seasonStat['stats'] == null) {
                    continue;
                }

                if (($this->position == "QB" ||$this->position == "WR" || $this->position == "TE" || $this->position == "RB") && array_key_exists('gp', $seasonStat['stats'])) {
                    $tableData[] = [
                        "year" => $year,
                        "stats" => $seasonStat['stats'],
                        "ranks" => $seasonStat['ranks']
                    ];
                }
            }
        } else {
            $tableData[] = [
                "year" => 2018,
                "stats" => [],
                "ranks" => []
            ];
        }

        return $tableData;
    }

    public function makeGameLogs()
    {
        $tableData = [];
        if (!empty($this->gameLogs)) {
            $gameLogs = $this->gameLogs;
            foreach ($gameLogs as $gameLog) {
                $stats = $gameLog->getStats();
                if ($stats == null || empty($stats)) {
                    continue;
                }
                if (!array_key_exists('off_snp', $stats)) {
                    continue;
                }

                if ($this->position == "QB" || $this->position == "WR" || $this->position == "TE" || $this->position == "RB") {
                    $tableData[] = [
                        'year' => $gameLog->getYear(),
                        'week' => $gameLog->getWeek(),
                        'stats' => $stats,
                        'ranks' => $gameLog->getRanks()
                    ];
                }
            }
        } else {
            $tableData[] = [
                'year' => '',
                'week' => '',
                'stats' => []
            ];
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
                        "recDom" => round(($stats['recs'] / $stats['totals']['recs']) * 100, 1)."%",
                        "ydDom" => round((($stats['rushYds'] + $stats['recYds']) / $stats['totals']['yds']) * 100, 1)."%",
                        "tdDom" => round((($stats['rushTds'] + $stats['recTds']) / $stats['totals']['tds']) * 100, 1)."%",
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

        $injuryInfo = $this->getInjuryInfo();

        if (array_key_exists("injury_status",$injuryInfo) && $injuryInfo["injury_status"] != null) {
            $this->status = substr($injuryInfo["injury_status"], 0, 1);
        } else {
            $this->status = "";
        }

        return get_object_vars($this);
    }

    private function makeOrdinals()
    {
        $ordinals = [];
        foreach($this->percentiles as $key => $value) {
            $nf = new \NumberFormatter('en_US', \NumberFormatter::ORDINAL);
            $value = round($value);
            $ordinals[$key] = $nf->format($value);
        }
        return $ordinals;
    }

    public function prepForMysql()
    {
        if (is_array($this->player_info)) {
            foreach ($this->player_info as $key => $value) {
                $this->player_info[$key] = addslashes($value);
            }
        }
    }

    public function fillEmptyValues()
    {
        $infoArray = [
            'arms',
            'bmi',
            'draft_pick',
            'draft_year',
            'college',
            'collegeSeasons',
            'heightInches',
            'hands',
            'arms',
            'weight',
            'heightInches'
        ];
        foreach($infoArray as $metric) {
            if (!array_key_exists($metric, $this->player_info)) {
                $this->player_info[$metric] = "N/A";
                $this->ordinals[$metric] = null;
            }
        }


        $metricsArray = [
            'fortyTime',
            'cone',
            'shuttle',
            'verticalJump',
            'broadJump',
            'benchPress',
            'collegeScore',
            'bully',
            'elusiveness',
            'power',
            'jumpball',
            'routeAgility',
            'jukeAgility',
            'breakoutClass',
            'breakoutYears',
            'bestDominator',
        ];
        foreach($metricsArray as $metric) {
            if (!array_key_exists($metric, $this->metrics)) {
                $this->metrics[$metric] = "N/A";
                $this->percentiles[$metric] = null;
                $this->ordinals[$metric] = null;
            }
        }


    }

    private function makeImages()
    {
        $images = "";
        if (empty($this->images)) {
            $images = "<a class='carousel-item' href='#one!'><img class='sleeper_img' src='https://sleepercdn.com/content/nfl/players/{$this->sleeper_id}.jpg'  style='height:280px; margin:auto;'></a>";
        } else {
            $i = 0;
            foreach ($this->images as $key => $url) {
                if (empty($url) && $i == 0) {
                    $images = "<a class='carousel-item' href='#one!'><img class='sleeper_img' src='https://sleepercdn.com/content/nfl/players/{$this->sleeper_id}.jpg'  style='height:280px; margin:auto;'></a>";
                    break;
                }

                if (!empty($url)){
                    $images .= "<a class='carousel-item' href='#{$key}!'><img src='{$url}' style='height:280px'></a>\n";
                }

                $i++;
            }
        }
        $this->images = $images;
    }

    public function exchangeArray(array $data)
    {
        $this->id  = !empty($data['id']) ? $data['id'] : null;
        $this->first_name  = !empty($data['first_name']) ? $data['first_name'] : null;
        $this->last_name  = !empty($data['last_name']) ? $data['last_name'] : null;
        $this->team  = !empty($data['team']) ? $data['team'] : null;
        $this->position  = !empty($data['position']) ? $data['position'] : null;
        $this->player_info  = !empty($data['player_info']) ? $data['player_info'] : null;
        $this->team_info  = !empty($data['team_info']) ? $data['team_info'] : null;
        $this->api_info  = !empty($data['api_info']) ? $data['api_info'] : null;
        $this->injury_info  = !empty($data['injury_info']) ? $data['injury_info'] : null;
        $this->metrics     = !empty($data['metrics']) ? $data['metrics'] : null;
        $this->percentiles     = !empty($data['percentiles']) ? $data['percentiles'] : null;
        $this->college_stats    = !empty($data['college_stats']) ? $data['college_stats'] : null;
        $this->images = !empty($data['images']) ? $data['images']: null;
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

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf(
            '%s does not allow injection of an alternate input filter',
            __CLASS__
        ));
    }

    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }

        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'id',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
            ],
        ]);


        $this->inputFilter = $inputFilter;
        return $this->inputFilter;
    }
}