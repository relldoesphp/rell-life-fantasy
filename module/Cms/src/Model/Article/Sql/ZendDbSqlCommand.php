<?php

namespace Cms\Model\Article\Sql;

use RuntimeException;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Cms\Model\Article\ArticleCommandInterface;
use Cms\Model\Article\Article;


class ZendDbSqlCommand implements ArticleCommandInterface
{
    /**
     * @var AdapterInterface
     */
    private $db;

    /**
     * @param AdapterInterface $db
     */
    public function __construct(AdapterInterface $db)
    {
        $this->db = $db;
    }
    /**
     * {@inheritDoc}
     */
    public function insertPost(Article $post)
    {
        $time = strtotime($post->getPublishDate());
        $datetime = date("Y-m-d H:i:s", $time);

        $insert = new Insert('posts');
        $insert->values([
            'title' => $post->getTitle(),
            'text' => $post->getText(),
            'originalUrl' => $post->getOriginalUrl(),
            'summary' => $post->getSummary(),
            'headline' => $post->getHeadline(),
            'author' => $post->getAuthor(),
            'active' => $post->getActive(),
            'publishDate' => $datetime,
            'image' => $post->getImage()
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

        return new Article(
            $post->getTitle(),
            $post->getText(),
            $id
        );
    }

    /**
     * {@inheritDoc}
     */
    public function updatePost(Article $post)
    {
        if (! $post->getId()) {
            throw new RuntimeException('Cannot update post; missing identifier');
        }

        $time = strtotime($post->getPublishDate());
        $datetime = date("Y-m-d H:i:s", $time);

        $update = new Update('posts');
        $update->set([
            'title' => $post->getTitle(),
            'text' => $post->getText(),
            'originalUrl' => $post->getOriginalUrl(),
            'summary' => $post->getSummary(),
            'headline' => $post->getHeadline(),
            'author' => $post->getAuthor(),
            'active' => $post->getActive(),
            'publishDate' => $datetime,
            'image' => $post->getImage()
        ]);
        $update->where(['id = ?' => $post->getId()]);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        if (! $result instanceof ResultInterface) {
            throw new RuntimeException(
                'Database error occurred during blog post update operation'
            );
        }

        return $post;
    }

    /**
     * {@inheritDoc}
     */
    public function deletePost(Article $post)
    {
        if (! $post->getId()) {
            throw new RuntimeException('Cannot update post; missing identifier');
        }

        $delete = new Delete('posts');
        $delete->where(['id = ?' => $post->getId()]);

        $sql = new Sql($this->db);
        $statement = $sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();

        if (! $result instanceof ResultInterface) {
            return false;
        }

        return true;
    }
}