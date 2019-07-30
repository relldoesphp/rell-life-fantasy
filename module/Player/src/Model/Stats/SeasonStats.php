<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/8/19
 * Time: 4:20 PM
 */

namespace Player\Model\Stats;


class SeasonStats
{
    public $id;
    public $sleeper_id;
    public $year;
    public $stats;

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



}