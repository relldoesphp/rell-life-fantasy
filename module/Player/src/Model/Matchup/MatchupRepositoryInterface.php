<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 12:56 PM
 */

namespace Player\Model\Matchup;


interface MatchupRepositoryInterface
{
    public function getMatchupsByWeekYear($week, $year);

    public function getMatchupById($id);

    public function getMatchupsByTeamYear($name);
}