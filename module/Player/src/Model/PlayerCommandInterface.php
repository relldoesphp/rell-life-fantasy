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

    public function getWrCommand();

    public function getRbCommand();

    public function getTeCommand();

    public function getSleeperStats();

    public function getSleeperGameLogs();

    public function makePlayerNameJson();

    public function updateSleeperInfo();


}