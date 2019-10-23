<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 12:58 PM
 */

namespace Player\Model\Matchup\Sql;


use Player\Model\Matchup\MatchupRepositoryInterface;


class SqlMatchupRepository implements MatchupRepositoryInterface
{

    private $db;
    private $prototype;
    private $hydrator;

    /**
     * SqlMatchupRepository constructor.
     * @param $db
     * @param $prototype
     */
    public function __construct($db, $hydrator, $prototype)
    {
        $this->db = $db;
        $this->prototype = $prototype;
        $this->hydrator = $hydrator;
    }

    /**
     * @param $week
     * @param $year
     * @return mixed
     */
    public function getMatchupsByWeekYear($week, $year)
    {
        // TODO: Implement getMatchupsByWeekYear() method.
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getMatchupById($id)
    {
        // TODO: Implement getMatchupById() method.
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getMatchupsByTeamYear($name)
    {
        // TODO: Implement getMatchupsByTeamYear() method.
    }

}