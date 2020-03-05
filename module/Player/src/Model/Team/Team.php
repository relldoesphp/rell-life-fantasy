<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/4/19
 * Time: 3:39 PM
 */

namespace Player\Model\Team;

use Laminas\Json\Json;

class Team
{
    private $id;
    public $team;
    public $city;
    public $name;
    public $coach;
    public $coach_type;
    public $off_coordinator;
    public $def_coordinator;
    public $off_base;
    public $def_base;
    public $depth_chart;
    public $personnelGroups;
    public $stats;
    public $metrics;

    /**
     * Team constructor.
     * @param $id
     */
    public function __construct(){}

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @param mixed $team
     */
    public function setTeam($team)
    {
        $this->team = $team;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getConference()
    {
        return $this->conference;
    }

    /**
     * @param mixed $conference
     */
    public function setConference($conference)
    {
        $this->conference = $conference;
    }

    /**
     * @return mixed
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * @param mixed $division
     */
    public function setDivision($division)
    {
        $this->division = $division;
    }

    /**
     * @return mixed
     */
    public function getDepthChart()
    {
        return $this->depth_chart;
    }

    /**
     * @param mixed $depthChart
     */
    public function setDepthChart($depthChart)
    {
        $this->depth_chart = $depthChart;
    }

    /**
     * @return mixed
     */
    public function getCoaches()
    {
        return $this->coaches;
    }

    /**
     * @param mixed $coaches
     */
    public function setCoaches($coaches)
    {
        $this->coaches = $coaches;
    }

    /**
     * @return mixed
     */
    public function getDefScheme()
    {
        return $this->defScheme;
    }

    /**
     * @param mixed $defScheme
     */
    public function setDefScheme($defScheme)
    {
        $this->defScheme = $defScheme;
    }

    /**
     * @return mixed
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * @param mixed $stats
     */
    public function setStats($stats)
    {
        $this->stats = $stats;
    }

    /**
     * @return mixed
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * @param mixed $metrics
     */
    public function setMetrics($metrics)
    {
        $this->metrics = $metrics;
    }

    public function decodeJson()
    {
        if (!is_array($this->depth_chart)) {
            $this->depth_chart = (!empty($this->depth_chart)) ? Json::decode($this->depth_chart, 1) : [];
        }
    }

    public function encodeJson()
    {
        if (is_array($this->depth_chart)) {
            $this->depth_chart = Json::encode($this->depth_chart);
        }
    }
}