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
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\ProgressBar\Adapter\Console;


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
        } else {
            $this->createTeam($team);
        }
    }

    /**
     * @param Team $team
     * @return mixed
     */
    public function createTeam(Team $team)
    {
        $team->encodeJson();

        /** Insert new player **/
        $sql    = new Sql($this->db);
        $insert = $sql->insert('teams');
        $insert->values([
            "team" => $team->getTeam(),
            "city" => $team->getCity(),
            "name" => $team->getName(),
            "coaches" => $team->getCoaches(),
            "scheme" => $team->getScheme(),
            "ratings" => $team->getRatings(),
            "volume" => $team->getVolume(),
            "roles" => $team->getRoles(),
            "sis_info" => $team->getSisInfo()
            //   "depth_chart" => $team->depth_chart
        ]);

        $stmt = $sql->prepareStatementForSqlObject($insert);
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
            "coaches" => $team->getCoaches(),
            "scheme" => $team->getScheme(),
            "stats" => $team->getStats(),
            "ratings" => $team->getRatings(),
            "volume" => $team->getVolume(),
            "roles" => $team->getRoles(),
            "sis_info" => $team->getSisInfo()
         //   "depth_chart" => $team->depth_chart
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