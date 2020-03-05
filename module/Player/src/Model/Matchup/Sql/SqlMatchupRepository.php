<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 12:58 PM
 */

namespace Player\Model\Matchup\Sql;


use Player\Model\Matchup\MatchupRepositoryInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;



class SqlMatchupRepository implements MatchupRepositoryInterface
{

    private $db;
    private $prototype;
    private $hydrator;

    /**
     * SqlMatchupRepository constructor.
     * @param $db
     * @param $prototype
     */
    public function __construct(AdapterInterface $db, HydratorInterface $hydrator, $prototype)
    {
        $this->db = $db;
        $this->prototype = $prototype;
        $this->hydrator = $hydrator;
    }

    /**
     * @param $week
     * @param $year
     * @return mixed
     */
    public function getMatchupsByWeekYear($week, $year)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['m' => 'matchups']);
        $select->where([
            'm.week = ?' => $week,
            'm.year = ?' => $year
        ]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->prototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getMatchupById($id)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['m' => 'matchups']);
        $select->where([
            'm.id = ?' => $id,
        ]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->prototype);
        $resultSet->initialize($result);
        return $resultSet->current();
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getMatchupsByTeamYear($name)
    {
        // TODO: Implement getMatchupsByTeamYear() method.
    }

}