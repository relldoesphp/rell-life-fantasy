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

    public function makeSeasonRanks($year, $position, $types);

    public function makeWeeklyRanks($week, $position, $types);
}