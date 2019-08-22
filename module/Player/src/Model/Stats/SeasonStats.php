<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/8/19
 * Time: 4:20 PM
 */

namespace Player\Model\Stats;

use Zend\Json\Json;

class SeasonStats
{
    public $id;
    public $player_id;
    public $sleeper_id;
    public $year;
    public $stats;
    public $ranks;
    public $notes;

    /**
     * @return mixed
     */
    public function getPlayerId()
    {
        return $this->player_id;
    }

    /**
     * @param mixed $player_id
     */
    public function setPlayerId($player_id)
    {
        $this->player_id = $player_id;
    }

    /**
     * @return mixed
     */
    public function getRanks()
    {
        return $this->ranks;
    }

    /**
     * @param mixed $ranks
     */
    public function setRanks($ranks)
    {
        $this->ranks = $ranks;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }


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
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getSleeperId()
    {
        return $this->sleeper_id;
    }

    /**
     * @param mixed $sleeper_id
     */
    public function setSleeperId($sleeper_id)
    {
        $this->sleeper_id = $sleeper_id;
    }

    /**
     * @return mixed
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param mixed $year
     */
    public function setYear($year)
    {
        $this->year = $year;
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
        if (!is_array($this->ranks)) {
            $this->ranks = (!empty($this->ranks)) ? Json::decode($this->ranks, 1) : ['' => ''];
        }

        if (!is_array($this->stats)) {
            $this->stats = (!empty($this->stats)) ? Json::decode($this->stats, 1) : [];
        }
    }

    public function encodeJson()
    {
        if (is_array($this->ranks) || is_object($this->ranks)) {
            $this->ranks = Json::encode($this->ranks);
        }

        if (is_array($this->stats) || is_object($this->stats)) {
            $this->stats = Json::encode($this->stats);
        }
    }
}