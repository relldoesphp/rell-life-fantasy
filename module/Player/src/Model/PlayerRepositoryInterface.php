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

    public function findPlayer($id);

    public function getPlayerMetrics($id);

    public function getPlayerPercentiles($id);

}