<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/11/19
 * Time: 1:42 AM
 */

namespace Player\Model\Stats;

interface StatsCommandInterface
{
    public function saveGameLog(GameLog $gameLog);

    public function createGameLog(GameLog $gameLog);

    public function updateGameLog(GameLog $gameLog);

    public function deleteGameLog(GameLog $gameLog);

    public function saveSeasonStat(SeasonStats $seasonStat);

    public function createSeasonStat(SeasonStats $seasonStat);

    public function updateSeasonStat(SeasonStats $seasonStat);

    public function deleteSeasonStat(SeasonStats $seasonStat);
}