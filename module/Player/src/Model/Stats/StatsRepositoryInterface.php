<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/11/19
 * Time: 1:41 AM
 */

namespace Player\Model\Stats;

interface StatsRepositoryInterface
{
    public function getGameLogsById($id);

    public function getGameLogsBySleeperId($sleeperId);

    public function makeSeasonRanks($year, $position);

    public function makeWeeklyRanks($week, $year, $position);

    public function getSeasonStatsByPosition($position, $year);

    public function getGameLogsByPosition($position, $year, $week);

    public function getGameLogsByWeekYearSleeper($week, $year, $sleeperId);

    public function getSeasonStatsByPlayerId($playerId);

    public function getSeasonStatsBySleeperId($sleeperId);

    public function getSeasonStatsByWhere($where);

    public function getGameLogsByPlayerId($playerId);
}