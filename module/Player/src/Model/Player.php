<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 3:40 PM
 */

namespace Player\Model;

use Zend\Json\Json;

class Player
{
    /**
     * @var
     */
    private $id;
    private $first_name;
    private $last_name;
    private $search_full_name;
    private $team;
    private $position;
    private $player_info;
    private $team_info;
    private $api_info;
    private $injury_info;
    private $metrics;
    private $percentiles;
    private $college_stats;
    private $sleeper_id;
    public $collegeTable;
    public $ordinals;
    public $seasonStats;
    public $images;

    public function __construct()
    {
        $this->player_info = [
            'arms' => '',
            'draft_pick' => '',
            'draft_year' => '',
        ];

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
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
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

    public function makeSeasonStats()
    {
        $tableData = [];
        if (!empty($this->seasonStats['0']['stats'])) {
            $seasonStats = $this->seasonStats;
            foreach ($seasonStats as $seasonStat) {
                $stats = Json::decode($seasonStat['stats'], 1);
                if ($this->position == "WR") {
                    $tableData[] = [
                        $seasonStat['year'],
                        $stats['gp'],
                        round($stats['pts_ppr']/$stats['gp'], 1),
                        $stats['rec']." (".round($stats['rec']/$stats['gp'], 1)." p/g)",
                        $stats['rec_yd']." (".round($stats['rec_yd']/$stats['gp'], 1)." p/g)",
                        $stats['rec_td'],
                        $stats['rec_tgt']." (".round($stats['rec_tgt']/$stats['gp'], 1)." p/g)",
                        $stats['rec_ypr'],
                        $stats['rec_ypt'],
                        round($stats['rec_yd']/$stats['rec'], 1)
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
                if ($this->position == "WR") {
                    $tableData[] = [
                        $year,
                        $stats['college'],
                        $stats['class'],
                        $stats['games'],
                        $stats['recs'],
                        $stats['recYds'],
                        $stats['recTds'],
                        $stats['recAvg'],
                        round($stats['recs']/$stats['totals']['recs'] * 100,1)."%",
                        round($stats['recYds']/$stats['totals']['yds'] * 100, 1)."%",
                        round($stats['recTds']/$stats['totals']['tds'] * 100,1)."%",
                        0,
                        0
                    ];
                }

                if ($this->position == "TE") {
                    $tableData[] = [
                        $year,
                        $stats->college,
                        $stats->class,
                        $stats->games,
                        $stats->receptions,
                        $stats->recYds,
                        $stats->recTds,
                        $stats->recAvg,
                        round($stats->recDominator,1)."%",
                        round($stats->ydsDominator,1)."%",
                        round($stats->tdDominator,1)."%",
                    ];
                }

                if ($this->position == "RB") {
                    $tableData[] = [
                        $year,
                        $stats->college,
                        $stats->class,
                        $stats->games,
                        $stats->rushAtt,
                        $stats->rushYds,
                        $stats->rushAvg,
                        $stats->rushTds,
                        $stats->recs,
                        $stats->recYds,
                        $stats->recAvg,
                        $stats->recTds,
                        round(($stats->rushAtt / $stats->totals->carries) * 100, 1)."%",
                        round($stats->recDominator,1)."%",
                        round($stats->ydsDominator,1)."%",
                        round($stats->tdDominator,1)."%",
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
        $this->fillEmptyValues();
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
}