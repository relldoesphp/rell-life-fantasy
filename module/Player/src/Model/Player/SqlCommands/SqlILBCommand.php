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

class SqlILBCommand extends SqlPlayerAbstract
{
    public function calculateSpecialScores($type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where->in("position", ["ILB"]);
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
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function calculateSpecialPercentiles(){

        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.runStop'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.runStop'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.runStop' != '0' AND position in ('ILB')
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $runStop = [];
        foreach($resultSet as $row) {
            $runStop[$row->id] = $row->percentile_rank;
        }
        print "run stop built\n";

        /*****************************************************************************/
        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.insideRush'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.insideRush'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.insideRush' != '0' AND position in ('ILB')
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $insideRush = [];
        foreach($resultSet as $row) {
            $insideRush[$row->id] = $row->percentile_rank;
        }
        print "inside rush built\n";

        /*****************************************************************************/
        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.coverage'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.coverage'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.coverage' != '0' AND position in ('ILB')
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
        print "coverage built\n";


        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where->in("position", ["ILB"]);
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
            $data['insideRush'] = (array_key_exists($id, $insideRush)) ? $insideRush[$id] * 100 : "";
            $data['runStop'] = (array_key_exists($id, $runStop)) ? $runStop[$id] * 100 : "";
            $data['coverage'] = (array_key_exists($id, $coverage)) ? $coverage[$id] * 100 : "";

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