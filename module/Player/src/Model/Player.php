<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 3:40 PM
 */

namespace Player\Model;

class Player
{
    /**
     * @var
     */
    private $id;
    private $firstName;
    private $lastName;
    private $team;
    private $height;
    private $height_inches;
    private $weight;
    private $arms;
    private $arms_inches;
    private $age;
    private $college;
    private $draftPick;
    private $draftYear;
    private $metrics;
    private $percentiles;

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
        return $this->firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return mixed
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @return mixed
     */
    public function getHeightInches()
    {
        return $this->height_inches;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return mixed
     */
    public function getArms()
    {
        return $this->arms;
    }

    /**
     * @return mixed
     */
    public function getArmsInches()
    {
        return $this->arms_inches;
    }

    /**
     * @return mixed
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @return mixed
     */
    public function getCollege()
    {
        return $this->college;
    }

    /**
     * @return mixed
     */
    public function getDraftPick()
    {
        return $this->draftPick;
    }

    /**
     * @return mixed
     */
    public function getDraftYear()
    {
        return $this->draftYear;
    }

    /**
     * @return mixed
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * @return mixed
     */
    public function getPercentiles()
    {
        return $this->percentiles;
    }

}