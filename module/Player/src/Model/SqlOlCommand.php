<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 6/5/19
 * Time: 4:17 PM
 */

namespace Player\Model;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\ProgressBar\ProgressBar;
use Zend\ProgressBar\Adapter\Console;
use Zend\Db\Sql\Select;
use Zend\Http\Request;
use Zend\Http\Client;
use Zend\Dom\Query;

class SqlOlCommand extends SqlPlayerAbstract
{
    public function calculateSpecialScores()
    {

    }

    public function calculateSpecialPercentiles()
    {

    }
}