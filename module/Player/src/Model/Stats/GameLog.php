<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/8/19
 * Time: 4:20 PM
 */

namespace Player\Model\Stats;
use Laminas\Json\Json;

class GameLog
{
    private $id;
    private $player_id;
    private $sleeper_id;
    private $year;
    private $week;
    private $stats;
    private $team;
    private $opponent;
    private $ranks;
    private $home;
    private $away;
    private $notes;

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
    public function getHome()
    {
        return $this->home;
    }

    /**
     * @param mixed $home
     */
    public function setHome($home)
    {
        $this->home = $home;
    }

    /**
     * @return mixed
     */
    public function getAway()
    {
        return $this->away;
    }

    /**
     * @param mixed $away
     */
    public function setAway($away)
    {
        $this->away = $away;
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
    public function getOpponent()
    {
        return $this->opponent;
    }

    /**
     * @param mixed $opponent
     */
    public function setOpponent($opponent)
    {
        $this->opponent = $opponent;
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
    public function getWeek()
    {
        return $this->week;
    }

    /**
     * @param mixed $week
     */
    public function setWeek($week)
    {
        $this->week = $week;
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