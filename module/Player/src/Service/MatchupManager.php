<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 4:40 PM
 */

namespace Player\Service;

use Zend\Db\Adapter\AdapterInterface;
use Zend\ProgressBar\Adapter\Console;
use Player\Model\Matchup\MatchupCommandInterface;
use Player\Model\Matchup\MatchupRepositoryInterface;
use Player\Model\Stats\StatsCommandInterface;
use Player\Model\Stats\StatsRepositoryInterface;


class MatchupManager
{

    private $db;
    private $consoleAdapter;
    private $matchupCommand;
    private $matchupRepository;
    private $statsCommand;
    private $statsRepository;

    public function __construct(
        AdapterInterface $db,
        Console $consoleAdapter,
        MatchupCommandInterface $matchupCommand,
        MatchupRepositoryInterface $matchupRepository,
        StatsCommandInterface $statsCommand,
        StatsRepositoryInterface $statsRepository
    )
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
        $this->matchupRepository = $matchupRepository;
        $this->matchupCommand = $matchupCommand;
        $this->statsCommand = $statsCommand;
        $this->statsRepository = $statsRepository;
    }

}