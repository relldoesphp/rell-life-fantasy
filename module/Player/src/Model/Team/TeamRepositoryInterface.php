<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/11/19
 * Time: 5:36 PM
 */

namespace Player\Model\Team;


interface TeamRepositoryInterface
{
    public function getTeams();

    public function getTeamById($id);

    public function getTeamByName($name);
}