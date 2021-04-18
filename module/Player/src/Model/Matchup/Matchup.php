<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 12:52 PM
 */

namespace Player\Model\Matchup;


class Matchup
{
    private $id;
    public $year;
    public $week;
    public $away;
    public $home;
    public $date;
    public $type;
    public $weather;
    public $over_under;
    public $away_roster;
    public $home_roster;
    public $away_score;
    public $home_score;
    public $result;
    public $plays;
    public $gameId;

    /**
     * @return mixed
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * @param mixed $gameId
     */
    public function setGameId($gameId)
    {
        $this->gameId = $gameId;
    }

    /**
     * Matchup constructor.
     */
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
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getWeather()
    {
        return $this->weather;
    }

    /**
     * @param mixed $weather
     */
    public function setWeather($weather)
    {
        $this->weather = $weather;
    }

    /**
     * @return mixed
     */
    public function getOverUnder()
    {
        return $this->over_under;
    }

    /**
     * @param mixed $over_under
     */
    public function setOverUnder($over_under)
    {
        $this->over_under = $over_under;
    }

    /**
     * @return mixed
     */
    public function getAwayRoster()
    {
        return $this->away_roster;
    }

    /**
     * @param mixed $away_roster
     */
    public function setAwayRoster($away_roster)
    {
        $this->away_roster = $away_roster;
    }

    /**
     * @return mixed
     */
    public function getHomeRoster()
    {
        return $this->home_roster;
    }

    /**
     * @param mixed $home_roster
     */
    public function setHomeRoster($home_roster)
    {
        $this->home_roster = $home_roster;
    }

    /**
     * @return mixed
     */
    public function getAwayScore()
    {
        return $this->away_score;
    }

    /**
     * @param mixed $away_score
     */
    public function setAwayScore($away_score)
    {
        $this->away_score = $away_score;
    }

    /**
     * @return mixed
     */
    public function getHomeScore()
    {
        return $this->home_score;
    }

    /**
     * @param mixed $home_score
     */
    public function setHomeScore($home_score)
    {
        $this->home_score = $home_score;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getPlays()
    {
        return $this->plays;
    }

    /**
     * @param mixed $plays
     */
    public function setPlays($plays)
    {
        $this->plays = $plays;
    }
}