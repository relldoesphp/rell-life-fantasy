<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/22/19
 * Time: 7:48 AM
 */

namespace Player\Model;


/**
 * Interface PlayerCommandInterface
 * @package Player\Model
 */
interface PlayerCommandInterface
{
    public function addPlayer(Player $player);

    public function updatePlayer(Player $player);

    public function deletePlayer(Player $player);

    public function calculateMetrics(string $type);

    public function calculatePercentiles(string $type);
}