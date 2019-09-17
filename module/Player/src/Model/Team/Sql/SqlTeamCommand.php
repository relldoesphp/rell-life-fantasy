<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/11/19
 * Time: 5:47 PM
 */

namespace Player\Model\Team\Sql;

use Player\Model\Team\Team;
use Player\Model\Team\TeamCommandInterface;
use InvalidArgumentException;
use RuntimeException;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Hydrator\HydratorInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\ProgressBar\Adapter\Console;


class SqlTeamCommand implements TeamCommandInterface
{

    private $db;
    private $consoleAdapter;

    public function __construct(AdapterInterface $db, Console $consoleAdapter)
    {
        $this->db = $db;
        $this->consoleAdapter = $consoleAdapter;
    }

    /**
     * @param Team $team
     * @return mixed
     */
    public function saveTeam(Team $team)
    {
        if ($team->getId() != null) {
            $this->updateTeam($team);
        }
    }

    /**
     * @param Team $team
     * @return mixed
     */
    public function createTeam(Team $team)
    {
        // TODO: Implement createTeam() method.
    }

    /**
     * @param Team $team
     * @return mixed
     */
    public function updateTeam(Team $team)
    {
        $team->encodeJson();

        /** Insert new player **/
        $sql    = new Sql($this->db);
        $update = $sql->update('teams');
        $update->set([
            "team" => $team->getTeam(),
            "city" => $team->getCity(),
            "name" => $team->getName(),
            "depth_chart" => $team->depth_chart
        ]);
        $update->where(['id = ?' => $team->getId()]);
        $stmt = $sql->prepareStatementForSqlObject($update);
        try {
            $result = $stmt->execute();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param Team $team
     * @return mixed
     */
    public function deleteTeam(Team $team)
    {
        // TODO: Implement deleteTeam() method.
    }

}