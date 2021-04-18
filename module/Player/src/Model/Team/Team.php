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
    public $coaches;
    public $scheme;
    public $ratings;
    public $volume;
    public $roles;
    public $support;
    public $sustainability;
    public $personnel;
    public $stats;

    /**
     * @return mixed
     */
    public function getSisInfo()
    {
        return $this->sis_info;
    }

    /**
     * @param mixed $sis_info
     */
    public function setSisInfo($sis_info)
    {
        $this->sis_info = $sis_info;
    }
    public $depth_chart;
    public $sis_info;

    /**
     * @return mixed
     */
    public function getDepthChart()
    {
        return $this->depth_chart;
    }

    /**
     * @param mixed $depth_chart
     */
    public function setDepthChart($depth_chart)
    {
        $this->depth_chart = $depth_chart;
    }

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
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @param mixed $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * @return mixed
     */
    public function getRatings()
    {
        return $this->ratings;
    }

    /**
     * @param mixed $ratings
     */
    public function setRatings($ratings)
    {
        $this->ratings = $ratings;
    }

    /**
     * @return mixed
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * @param mixed $volume
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return mixed
     */
    public function getSupport()
    {
        return $this->support;
    }

    /**
     * @param mixed $support
     */
    public function setSupport($support)
    {
        $this->support = $support;
    }

    /**
     * @return mixed
     */
    public function getSustainability()
    {
        return $this->sustainability;
    }

    /**
     * @param mixed $sustainability
     */
    public function setSustainability($sustainability)
    {
        $this->sustainability = $sustainability;
    }

    /**
     * @return mixed
     */
    public function getPersonnel()
    {
        return $this->personnel;
    }

    /**
     * @param mixed $personnel
     */
    public function setPersonnel($personnel)
    {
        $this->personnel = $personnel;
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


    public function decodeJson()
    {
        $jsonFields = [
            'coaches',
            'depth_chart',
            'scheme',
            'ratings',
            'volume',
            'roles',
            'support',
            'sustainability',
            'personnel',
            'sis_info',
            'stats'
        ];

        foreach ($jsonFields as $field) {
            if (!is_array($this->$field)) {
                $this->$field = (!empty($this->$field)) ? Json::decode($this->$field, 1) : [];
            }
        }
    }

    public function encodeJson()
    {
        $jsonFields = [
            'coaches',
            'depth_chart',
            'scheme',
            'ratings',
            'volume',
            'roles',
            'support',
            'sustainability',
            'personnel',
            'sis_info',
            'stats'
        ];

        foreach ($jsonFields as $field) {
            if (is_array($this->$field)) {
                $this->$field = Json::encode($this->$field);
            }
        }
    }
}