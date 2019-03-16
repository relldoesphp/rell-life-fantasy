<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 4:19 PM
 */

namespace Player\Model;


interface PlayerRepositoryInterface
{
    public function getPlayerNames();

    public function findAllPlayers();

    public function findPlayer();

    public function _getPlayerMetrics();

    public function _getPlayerPercentiles();

}