<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 6/5/19
 * Time: 4:17 PM
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

class SqlOlCommand extends SqlPlayerAbstract
{
    public function calculateSpecialScores($type)
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where->in("position", ["C","G","OT"]);
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

            $data['runBlock'] = ($percentiles->bully * .40) + ($percentiles->power * .60);
            $data['edgeBlock'] = ($percentiles->bully * .30) + ($percentiles->speedScore * .70);
            $data['insideBlock'] = ($percentiles->bully * .30) + ($percentiles->elusiveness * .70);

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
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.edgeBlock'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.edgeBlock'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.edgeBlock' != '0' AND position in ('OT', 'G', 'C')
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $runBlock = [];
        foreach($resultSet as $row) {
            $runBlock[$row->id] = $row->percentile_rank;
        }
        print "run Block built\n";

        /*****************************************************************************/
        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.passBlock'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.passBlock'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.passBlock' != '0' AND position in ('OT', 'G', 'C')
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $passBlock = [];
        foreach($resultSet as $row) {
            $passBlock[$row->id] = $row->percentile_rank;
        }
        print "pass Block built\n";

        /*****************************************************************************/
        $sql = <<<EOT
SELECT id, first_name, last_name, lpad(json_unquote(metrics->'$.insideBlock'),6,'0'),  PERCENT_RANK() OVER (ORDER BY lpad(json_unquote(metrics->'$.insideBlock'),6,'0')) percentile_rank
FROM player_test
WHERE metrics->'$.insideBlock' != '0' AND position in ('OT', 'G', 'C')
EOT;
        $stmt= $this->db->query($sql);
        $result = $stmt->execute();
        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $insideBlock = [];
        foreach($resultSet as $row) {
            $insideBlock[$row->id] = $row->percentile_rank;
        }
        print "pass Block built\n";


        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'player_test']);
        $select->where->in("position", ["C","G","OT"]);
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
            $data['insideBlock'] = (array_key_exists($id, $insideBlock)) ? $insideBlock[$id] * 100 : "";
            $data['runBlock'] = (array_key_exists($id, $runBlock)) ? $runBlock[$id] * 100 : "";
            $data['passBlock'] = (array_key_exists($id, $passBlock)) ? $passBlock[$id] * 100 : "";

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

    public function scrapCollegeJob()
    {
        $sql    = new Sql($this->db);
        $select = $sql->select();
        $select->from(['p' => 'players']);
        $select->where([
            'p.position = ?' => 'TE',
            'collegeStats' => null
        ]);
        $stmt   = $sql->prepareStatementForSqlObject($select);
        $result = $stmt->execute();

        if (! $result instanceof ResultInterface || ! $result->isQueryResult()) {
            return [];
        }

        $resultSet = new ResultSet();
        $resultSet->initialize($result);
        $count = $resultSet->count();
        $wrs = $resultSet->toArray();

        $progressBar = new ProgressBar($this->consoleAdapter, 0, $resultSet->count());
        $pointer = 0;

        foreach ($wrs as $wr) {
            $result = $this->scrapCollegeStats($wr);
            if ($result == false) {
                continue;
            }
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

}