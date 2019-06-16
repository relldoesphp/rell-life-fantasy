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

    public function decodeJson()
    {
        $this->player_info = (!empty($this->player_info)) ? Json::decode($this->player_info, 1) : "";
        $this->team_info = (!empty($this->team_info)) ? Json::decode($this->team_info, 1) : "";
        $this->api_info = (!empty($this->api_info)) ? Json::decode($this->api_info, 1) : "";
        $this->injury_info = (!empty($this->injury_info)) ? Json::decode($this->injury_info, 1) : "";
        $this->metrics = (!empty($this->metrics)) ? Json::decode($this->metrics, 1) : "";
        $this->percentiles = (!empty($this->percentiles)) ? Json::decode($this->percentiles, 1) : "";
        $this->college_stats = (!empty($this->college_stats)) ? Json::decode($this->college_stats, 1) : "";
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
}