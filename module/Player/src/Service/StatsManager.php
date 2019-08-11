<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/11/19
 * Time: 2:13 AM
 */

namespace Player\Service;

use Player\Model\Player\Player;
use Player\Model\Stats\StatsCommandInterface;
use Player\Model\Stats\StatsRepositoryInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Json\Json;
use Zend\ProgressBar\Adapter\Console;
use Zend\Http\Request;
use Zend\Http\Client;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Zend\ProgressBar\ProgressBar;
use Player\Model\Stats\SeasonStats;
use Player\Model\Stats\GameLog;

class StatsManager
{
    private $db;
    private $consoleAdapter;
    private $playerCommand;
    private $playerRepository;

    public function __construct(AdapterInterface $db,
                                Console $consoleAdapter,
                                PlayerCommandInterface $playerCommand,
                                PlayerRepositoryInterface $playerRepository,
                                StatsCommandInterface $statsCommand,
                                StatsRepositoryInterface $statsRepository
    )
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
        $this->playerRepository = $playerRepository;
        $this->playerCommand = $playerCommand;
        $this->statsRepository = $statsRepository;
        $this->statsCommand = $statsCommand;
    }
}