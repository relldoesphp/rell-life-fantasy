<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 4/5/19
 * Time: 11:34 PM
 */

namespace Player\Model\Player\SqlCommands;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\ProgressBar\ProgressBar;
use Laminas\ProgressBar\Adapter\Console;
use Laminas\Db\Sql\Select;
use Laminas\Http\Request;
use Laminas\Http\Client;
use Laminas\Dom\Query;

class SqlFSCommand extends SqlPlayerAbstract
{
    public function calculateSpecialScores()
    {

    }

    public function calculateSpecialPercentages()
    {

    }
}