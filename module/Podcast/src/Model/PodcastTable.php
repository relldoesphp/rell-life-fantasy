<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/24/19
 * Time: 8:32 PM
 */

namespace Podcast\Model;

use RuntimeException;
use Laminas\Db\TableGateway\TableGatewayInterface;

class PodcastTable
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    public function getPodcast($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['id' => $id]);
        $row = $rowset->current();
        if (! $row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }

        return $row;
    }

    public function savePodcast(Podcast $podcast)
    {
        $data = [
            'title' => $podcast->title,
            'description'  => $podcast->description,
            'audio_url' => $podcast->audio_url,
            'published' => $podcast->published,
            'create_date' => $podcast->create_date,
            'publish_date' => $podcast->publish_date,
        ];

        $id = (int) $podcast->id;

        if ($id === 0) {
            $this->tableGateway->insert($data);
            return;
        }

        try {
            $this->getPodcast($id);
        } catch (RuntimeException $e) {
            throw new RuntimeException(sprintf(
                'Cannot update podcast with identifier %d; does not exist',
                $id
            ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
    }

    public function deletePodcast($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }
}