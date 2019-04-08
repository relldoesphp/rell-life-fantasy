<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/22/19
 * Time: 7:59 AM
 */

namespace Player\Model;

use InvalidArgumentException;
use RuntimeException;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\ProgressBar\ProgressBar;
use Zend\ProgressBar\Adapter\Console;
use Zend\Db\Sql\Select;

class SqlPlayerCommand implements PlayerCommandInterface
{

    private $db;
    private $consoleAdapter;
    private $wrCommand;
    private $rbCommand;
    private $teCommand;

    public function __construct(AdapterInterface $db, Console $consoleAdapter)
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
        $this->wrCommand = new SqlWrCommand($db, $consoleAdapter);
        $this->rbCommand = new SqlRbCommand($db, $consoleAdapter);
        $this->teCommand = new SqlTeCommand($db, $consoleAdapter);
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function addPlayer(Player $player)
    {
        // TODO: Implement addPlayer() method.
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function updatePlayer(Player $player)
    {
        // TODO: Implement updatePlayer() method.
    }

    /**
     * @param Player $player
     * @return mixed
     */
    public function deletePlayer(Player $player)
    {
        // TODO: Implement deletePlayer() method.
    }

    public function getWrCommand()
    {
        return $this->wrCommand;
    }

    public function getRbCommand()
    {
        return $this->rbCommand;
    }

    public function getTeCommand()
    {
        return $this->teCommand;
    }
}