<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 4/5/19
 * Time: 11:34 PM
 */

namespace Player\Model\Player\SqlCommands;

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


class SqlCBCommand extends SqlPlayerAbstract
{

    public $specialMetrics = [
        'runStop' => [
            'field' => 'metrics',
            'sort' => 'DESC'
        ],
        'insideRush' => [
            'field' => 'metrics',
            'sort' => 'DESC'
        ],
        'coverage' => [
            'field' => 'metrics',
            'sort' => 'DESC'
        ],
        'pressCoverage' => [
            'field' => 'metrics',
            'sort' => 'DESC'
        ],
        'slotCoverage' => [
            'field' => 'metrics',
            'sort' => 'DESC'
        ],
        'outsideCoverage' => [
            'field' => 'metrics',
            'sort' => 'DESC'
        ]
    ];

    public function calculateSpecialScores($type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where->in("position", ["CB"]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $tes = $resultSet->toArray();
        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;
        foreach ($tes as $te) {
            $info = json_decode($te['player_info']);
            $metrics = json_decode($te['metrics']);
            $percentiles = json_decode($te['percentiles']);

            if ($metrics->shuttle != null && $metrics->cone != null) {
                $data['jukeAgility'] = ($percentiles->shuttle * .70) + ($percentiles->cone * .30);
                $data['routeAgility'] = ($percentiles->shuttle * .30) + ($percentiles->cone * .70);
            } else {
                $data['jukeAgility'] = '';
                $data['routeAgility'] = '';
            }

            $data['runStop'] = ($percentiles->bully * .20) + ($percentiles->power * .50) + ($percentiles->cone * .30);
            $data['insideRush'] = ($percentiles->bully * .30) + ($percentiles->elusiveness * .70);
            $data['coverage'] = ($data['routeAgility'] * .40) + ($percentiles->fortyTime * .60);

            $data['outsideCoverage'] = ($percentiles->bully * .20) + ($data['jukeAgility'] * .30) + ($percentiles->fortyTime * .40) + ($percentiles->jumpball * .10);
            $data['slotCoverage'] = ($data['routeAgility'] * .60) + ($percentiles->fortyTime * .40);
            $data['pressCoverage'] =  ($percentiles->bully * .50) + ($percentiles->power * .25) + ($percentiles->shuttle * .25);

            if ($percentiles->arms > 70) {
                $data['coverage'] = $data['coverage'] * 1.10;
            }

            $jsonString = "";
            foreach ($data as $key => $value) {
                if ($value < 0) {
                    $value = 0;
                }
                $jsonString .= ", '$.{$key}', '{$value}'";
            }

            $update = <<<EOT
UPDATE player_test SET metrics = json_set(metrics{$jsonString}) where id = {$te['id']};
EOT;
            $stmt = $this->db->query($update);
            $stmt->execute();

            $update = $sql->update('player_test');
            $update->set(['percentiles' => new Expression("json_set(percentiles{$jsonString})")]);
            $update->where(["id = ?", $id]);
            $stmt   = $sql->prepareStatementForSqlObject($update);
            $result = $stmt->execute();



            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function calculateSpecialPercentiles()
    {
        $percentiles = [];
        foreach ($this->specialMetrics as $name => $value) {
            $sql    = new Sql($this->db);
            $select = $sql->select([
                'id',
                'percentile_rank' => new Expression("PERCENT_RANK() OVER (ORDER BY lpad(json_unquote({$value['field']}->'$.{$name}'),6,'0') {$value['sort']})")
            ]);
            $select->from(['p' => 'player_test']);
            $select->where
                ->notEqualTo("{$value['field']}->'$.{$name}'", 0)
                ->in("position", ["CB"]);

            $stmt = $sql->prepareStatementForSqlObject($select);
            $result = $stmt->execute();
            if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
                return [];
            }

            $resultSet = new ResultSet();
            $resultSet->initialize($result);
            $percentiles[$name] = [];
            foreach($resultSet as $row) {
                $percentiles[$name][$row->id] = $row->percentile_rank;
            }
            print "{$name} built\n";
        }

        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where->in("position", ["CB"]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $count = $resultSet->count();
        $players = $resultSet->toArray();
        print "Percentages started\n";

        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;
        foreach ($players as $player) {
            $data = [];
            $id = $player['id'];

            foreach ($percentiles as $name => $value) {
                $data[$name] = (array_key_exists($id, $percentiles[$name])) ? $percentiles[$name][$id] * 100 : "";
            }

            $jsonString = "";
            foreach ($data as $key => $value) {
                $jsonString .= ", '$.{$key}', '{$value}'";
            }

            try {
                $update = $sql->update('player_test');
                $update->set(['percentiles' => new Expression("json_set(percentiles{$jsonString})")]);
                $update->where(["id = ?", $id]);
                $stmt   = $sql->prepareStatementForSqlObject($update);
                $result = $stmt->execute();
            } catch (\Exception $exception) {
                $message = $exception->getMessage();
            }

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Percentages completed\n";
    }
}