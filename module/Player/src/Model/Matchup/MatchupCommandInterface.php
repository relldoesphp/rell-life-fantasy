<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 12:56 PM
 */

namespace Player\Model\Matchup;

use Player\Model\Matchup\Matchup;

interface MatchupCommandInterface
{
    public function saveMatchup(Matchup $matchup);

    public function createMatchup(Matchup $matchup);

    public function updateMatchup(Matchup $matchup);

    public function deleteMatchup(Matchup $matchup);
}