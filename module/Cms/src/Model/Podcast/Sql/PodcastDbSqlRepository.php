<?php

namespace Cms\Model\Podcast\Sql;

use Cms\Model\Podcast\Podcast;
use Cms\Model\Podcast\PodcastRepositoryInterface;
use InvalidArgumentException;
use RuntimeException;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\Sql\Sql;

class PodcastDbSqlRepository implements PodcastRepositoryInterface
{
    private $db;

    private $hydrator;

    private $podcastPrototype;

    /**
     * PodcastDbSqlRepository constructor.
     */
    public function __construct(
        AdapterInterface $db,
        HydratorInterface $hydrator,
        Podcast $podcastPrototype
    )
    {
        $this->db = $db;
        $this->hydrator = $hydrator;
        $this->podcastPrototype = $podcastPrototype;
    }


    /**
     * @return mixed
     */
    public function findAllPodcasts()
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('podcast');
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->podcastPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findPodcast($id)
    {
        $sql       = new Sql($this->db);
        $select    = $sql->select('podcast');
        $select->where(['id = ?' => $id]);

        $statement = $sql->prepareStatementForSqlObject($select);
        $result    = $statement->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            throw new RuntimeException(sprintf(
                'Failed retrieving podcast post with identifier "%s"; unknown database error.',
                $id
            ));
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->podcastPrototype);
        $resultSet->initialize($result);
        $podcast = $resultSet->current();

        if (! $podcast) {
            throw new InvalidArgumentException(sprintf(
                'Podcast post with identifier "%s" not found.',
                $id
            ));
        }

        return $podcast;
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function findRecentPodcast($limit)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select('podcast');
        $select->order('id DESC');
        if (!empty($limit)) {
            $select->limit($limit);
        }
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new HydratingResultSet($this->hydrator, $this->podcastPrototype);
        $resultSet->initialize($result);
        return $resultSet;
    }

}