<?php

namespace Cms\Model\Podcast\Sql;

use Cms\Model\Podcast\Podcast;
use Cms\Model\Podcast\PodcastCommandInterface;
use RuntimeException;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;

class PodcastDbSqlCommand implements PodcastCommandInterface
{
    /**
     * @var AdapterInterface
     */
    private $db;

    /**
     * PodcastDbSqlCommand constructor.
     */
    public function __construct(AdapterInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param \Cms\Model\Podcast\Podcast $post
     * @return mixed
     */
    public function insertPost(\Cms\Model\Podcast\Podcast $podcast)
    {
        $createTime = date("Y-m-d H:i:s", strtotime('now'));

        $time = strtotime($podcast->getPublishDate());
        $publishTime = date("Y-m-d H:i:s", $time);

        $insert = new Insert('podcast');
        $insert->values([
            'title' => $podcast->getTitle(),
            'description' => $podcast->getDescription(),
            'audio_url' => $podcast->getAudioUrl(),
            'published' => $podcast->getPublished(),
            'create_date' => $createTime,
            'publish_date' => $publishTime,
        ]);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();

        if (! $result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post insert operation'
            );
        }

        $id = $result->getGeneratedValue();

        return new Podcast(
            $podcast->getTitle(),
            $id
        );

    }

    /**
     * @param \Cms\Model\Podcast\Podcast $post
     * @return mixed
     */
    public function updatePost(\Cms\Model\Podcast\Podcast $podcast)
    {
        if (! $podcast->getId()) {
            throw new RuntimeException('Cannot update post; missing identifier');
        }

        $time = strtotime($podcast->getPublishDate());
        $publishTime = date("Y-m-d H:i:s", $time);

        $update = new Update('podcast');
        $update->set([
            'title' => $podcast->getTitle(),
            'description' => $podcast->getDescription(),
            'audio_url' => $podcast->getAudioUrl(),
            'published' => $podcast->getPublished(),
            'publish_date' => $publishTime,
        ]);
        $update->where(['id = ?' => $podcast->getId()]);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        if (! $result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post update operation'
            );
        }

        return $podcast;
    }

    /**
     * @param \Cms\Model\Podcast\Podcast $post
     * @return mixed
     */
    public function deletePost(\Cms\Model\Podcast\Podcast $podcast)
    {
        if (! $podcast->getId()) {
            throw new RuntimeException('Cannot update post; missing identifier');
        }

        $delete = new Delete('podcast');
        $delete->where(['id = ?' => $podcast->getId()]);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();

        if (! $result instanceof ResultInterface) {
            return false;
        }

        return true;
    }

}