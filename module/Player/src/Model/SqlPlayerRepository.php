<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 4:44 PM
 */

namespace Player\Model;

use InvalidArgumentException;
use RuntimeException;
use Zend\Db\Adapter\AdapterInterface;

class SqlPlayerRepository implements PlayerRepositoryInterface
{
    /**
     * @var AdapterInterface
     */
    private $db;

    /**
     * ZendDbPlayerRespository constructor.
     */
    public function __construct(AdapterInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @return mixed
     */
    public function getPlayerNames()
    {
        // TODO: Implement getPlayerNames() method.
    }

    /**
     * @return mixed
     */
    public function findAllPlayers()
    {
        // TODO: Implement findAllPlayers() method.
    }

    /**
     * @return mixed
     */
    public function findPlayer()
    {
        // TODO: Implement findPlayer() method.
    }

    /**
     * @return mixed
     */
    public function _getPlayerMetrics()
    {
        // TODO: Implement _getPlayerMetrics() method.
    }

    /**
     * @return mixed
     */
    public function _getPlayerPercentiles()
    {
        // TODO: Implement _getPlayerPercentiles() method.
    }



}