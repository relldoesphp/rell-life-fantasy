<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/11/19
 * Time: 5:37 PM
 */

namespace Player\Model\Team;


interface TeamCommandInterface
{
    public function saveTeam(Team $team);

    public function createTeam(Team $team);

    public function updateTeam(Team $team);

    public function deleteTeam(Team $team);

}