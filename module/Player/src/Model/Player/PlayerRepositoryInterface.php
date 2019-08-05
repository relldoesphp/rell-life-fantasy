<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 4:19 PM
 */

namespace Player\Model\Player;


interface PlayerRepositoryInterface
{
    public function getPlayerNames($type);

    public function findAllPlayers($type);

    public function findPlayer($id);

    public function findPlayerByAlias($alias);

    public function getPlayerMetrics($id, $position);

    public function getPlayerPercentiles($id, $position);

    public function getTeamScores($team, $position);

    public function queryPlayers($query);

    public function getSeasonStats($sleeperId);

    public function findPlayerBySleeperId($sleeperId);

    public function getPercentileRanks($position, $metrics);

}