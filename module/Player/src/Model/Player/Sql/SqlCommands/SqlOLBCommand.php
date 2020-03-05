<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 4/5/19
 * Time: 11:34 PM
 */

namespace Player\Model\Player\SqlCommands;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\ProgressBar\ProgressBar;
use Laminas\ProgressBar\Adapter\Console;
use Laminas\Db\Sql\Select;
use Laminas\Http\Request;
use Laminas\Http\Client;
use Laminas\Dom\Query;

class SqlOLBCommand extends SqlPlayerAbstract
{
    public function calculateSpecialScores($type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where->in("position", ["OLB"]);
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

            $data['runStuff'] = ($percentiles->bully * .40) + ($percentiles->power * .60);
            $data['edgeRush'] = ($percentiles->bully * .30) + ($percentiles->speedScore * .70);
            $data['coverage'] = ($data['routeAgility'] * .50) + ($percentiles->fortyTime * .50);

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
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function calculateSpecialPercentiles(){

        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.runStuff'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.runStuff'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.runStugg' != '0' AND position in ('OLB')
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $runStuff = [];
        foreach($resultSet as $row) {
            $runStuff[$row->id] = $row->percentile_rank;
        }
        print "run stuff built\n";

        /*****************************************************************************/
        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.edgeRush'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.edgeRush'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.edgeRush' != '0' AND position in ('OLB')
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $edgeRush = [];
        foreach($resultSet as $row) {
            $edgeRush[$row->id] = $row->percentile_rank;
        }
        print "edge rush built\n";

        /*****************************************************************************/
        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.coverage'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.coverage'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.coverage' != '0' AND position in ('OLB')
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $coverage = [];
        foreach($resultSet as $row) {
            $coverage[$row->id] = $row->percentile_rank;
        }
        print "pass Block built\n";


        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where->in("position", ["OLB"]);
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
            $id = $player['id'];
            $data['insideBlock'] = (array_key_exists($id, $edgeRush)) ? $edgeRush[$id] * 100 : "";
            $data['runBlock'] = (array_key_exists($id, $runStuff)) ? $runStuff[$id] * 100 : "";
            $data['passBlock'] = (array_key_exists($id, $coverage)) ? $coverage[$id] * 100 : "";

            $jsonString = "";
            foreach ($data as $key => $value) {
                $jsonString .= ", '$.{$key}', '{$value}'";
            }

            try {
                $update = <<<EOT
UPDATE player_test SET percentiles = json_set(percentiles{$jsonString}) where id = {$id};
EOT;
                $stmt   = $this->db->query($update);
                $playerUpdated = $stmt->execute();
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