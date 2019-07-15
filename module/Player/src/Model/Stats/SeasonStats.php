<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/8/19
 * Time: 4:20 PM
 */

namespace Player\Model\Stats;


class SeasonStats
{
    public $id;
    public $sleeper_id;
    public $year;
    public $stats;

    public function __construct()
    {
        $this->stats = json_encode([
            'gp' => 0,
            'offSnp' => 0
        ]);
    }
}