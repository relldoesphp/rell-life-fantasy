<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/7/19
 * Time: 4:38 PM
 */

namespace Player\Service\Position;


use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\ProgressBar\Adapter\Console;

class TeService extends ServiceAbstract
{
    private $consoleAdapter;
    private $repository;
    private $command;
    private $db;

    public $specialMetrics = [
        'move' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'inLine' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ],
        'alpha' => [
            'field' => 'metrics',
            'sort' => 'ASC'
        ]
    ];

    public function __construct(AdapterInterface $db, Console $consoleAdapter, PlayerCommandInterface $command, PlayerRepositoryInterface $repository)
    {
        parent::__construct($db, $consoleAdapter, $command, $repository);
        $this->repository = $repository;
        $this->command = $command;
        $this->consoleAdapter = $consoleAdapter;
    }

    public function calculateSpecialMetrics()
    {
        $tes = $this->repository->findAllPlayers("TE");
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($tes));
        $pointer = 0;
        foreach ($tes as $te) {
            $te->decodeJson();
            if (empty($te->getMetrics())) {
                continue;
            }

            $info = $te->getPlayerInfo();
            $metrics = $te->getMetrics();
            $percentiles = $te->getPercentiles();

            /*** Calculate Run Block ***/
            $data['runBlock'] = null;
            if (!in_array($metrics['benchPress'], [null, "-", "", "null"])
                && !in_array($metrics['broadJump'], [null, "-", "", "null"]) ) {
                $data['runBlock'] = ($percentiles['bully'] * .30) + ($percentiles['power'] * .70);
            }

            if (in_array($metrics['benchPress'], [null, "-", "", "null"])
                && !in_array($metrics['broadJump'], [null, "-", "", "null"]) ) {
                $data['runBlock'] = $percentiles['power'];
            }

            if (!in_array($metrics['benchPress'], [null, "-", "", "null"])
                && in_array($metrics['broadJump'], [null, "-", "", "null"]) ) {
                $data['runBlock'] = $percentiles['benchPress'];
            }

            /*** Calculate Pass Block ***/
            if (!in_array($metrics['shuttle'], [null, "-", "", "null"])
                && !in_array($metrics['benchPress'], [null, "-", "", "null"]) ) {
                $data['passBlock'] = ($percentiles->bully * .40) + ($percentiles->elusiveness * .60);
            }

            if (in_array($metrics['shuttle'], [null, "-", "", "null"])
                && !in_array($metrics['benchPress'], [null, "-", "", "null"]) ) {
                $data['passBlock'] = ($percentiles->bully * .40) + ($percentiles->speedScore * .60);
            }

            /*** Calculate Move Score ***/
            //Move - TE Speed + Jumpball + Route Agility
            $data['move'] = 0;

            if (!in_array($metrics['routeAgility'], [null, "-", "", "null"])
                && !in_array($metrics['speed'], [null, "-", "", "null"]) ) {
                $data['move'] = ($percentiles['routeAgility'] * .70) + ($percentiles['speed'] * .30);
            }

            if (!in_array($metrics['routeAgility'], [null, "-", "", "null"])
                && in_array($metrics['speed'], [null, "-", "", "null"]) ) {
                $data['move'] = $percentiles['routeAgility'];
            }

            if (in_array($metrics['routeAgility'], [null, "-", "", "null"])
                && !in_array($metrics['speed'], [null, "-", "", "null"]) ) {
                $data['move'] = $percentiles['speed'];
            }

            if (!in_array($metrics['verticalJump'], [null, "-", "", "null"])) {
                $data['move'] = ($percentiles['verticalJump'] * .25) + ($data['move'] * .75);
            }

            /*** Calculate InLine Score ***/
            if (!in_array($data['runBlock'], [null, "-", "", "null"])
                && !in_array($percentiles['weight'], [null, "-", "", "null"]) ) {
                $data['inLine'] = ($percentiles['runBlock'] * .70) + ($percentiles['weight'] * .30);
            }

            if (in_array($data['runBlock'], [null, "-", "", "null"])
                && !in_array($percentiles['weight'], [null, "-", "", "null"]) ) {
                $data['inLine'] = ($percentiles['speedScore'] * .70) + ($percentiles['weight'] * .30);
            }

            //Alpha -  Move+Line
            $data['alpha'] = ($data['inLine'] + $data['move'])/2;

            $metrics['move'] = round($data['move'], 2);
            $metrics['inLine'] = round($data['inline'], 2);
            $metrics['alpha'] = round($data['alpha'], 2);

            $te->setMetrics($metrics);

            $this->command->save($te);
            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
    }

    public function calculateSpecialPercentiles($type)
    {
        $percentileArrays = $this->repository->getPercentileRanks($type, $this->specialMetrics);
        $players = $this->repository->findAllPlayers($type);
        $progressBar = new ProgressBar($this->consoleAdapter, 0, count($players));
        $pointer = 0;
        foreach ($players as $player) {
            $id = $player->getId();
            $player->decodeJson();
            $percentiles = $player->getPercentiles();
            foreach ($percentileArrays as $name => $value) {
                $percentiles[$name] = (array_key_exists($id, $percentileArrays[$name])) ? round($percentileArrays[$name][$id] * 100, 2) : "";
            }

            $player->setPercentiles($percentiles);

            $this->command->save($player);

            $pointer++;
            $progressBar->update($pointer);
        }
        $progressBar->finish();
        print "Percentiles completed\n";
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

    public function scrapCollegeStats($wr)
    {
        $request = new Request();
        if ($wr['cfb-alias'] == null) {
            $cfb = strtolower($wr['alias'])."-1";
        } else {
            $cfb = $wr['cfb-alias'];
        }
        $request->setUri("https://www.sports-reference.com/cfb/players/{$cfb}.html");

        $client = new Client();
        $response = $client->send($request);
        $html = $response->getBody();

        $dom = new Query($html);
        $results = $dom->execute('#receiving tr');

        $count = count($results);
        if ($count == 0) {
            return false;
        }
        $collegeStats = [];
        foreach ($results as $result) {
            $rowChildren = $result->childNodes;
            $firstItem = $rowChildren->item(1)->nodeValue;

            if (!empty($firstItem) && $firstItem != 'Year') {
//                if ($rowChildren->item(1)->nodeValue != $wr['college']) {
//                    return false;
//                }
                $year = $rowChildren->item(0)->nodeValue;
                $year = str_replace("*", "", $year);
                if (! $rowChildren->item(1)->firstChild instanceof \DOMElement) {
                    return false;
                }
                $collegeHref = $rowChildren->item(1)->firstChild->getAttribute("href");
                $totals = $this->getCollegeTotals($collegeHref);
                $collegeStats[$year]['totals'] = $totals;
                $collegeStats[$year]['year'] = $year;
                $collegeStats[$year]['college'] = $rowChildren->item(1)->nodeValue;
                $collegeStats[$year]['class'] = $rowChildren->item(3)->nodeValue;
                $collegeStats[$year]['games'] = $rowChildren->item(4)->nodeValue;
                $collegeStats[$year]['games'] = $rowChildren->item(5)->nodeValue;
                $collegeStats[$year]['receptions'] = $rowChildren->item(6)->nodeValue;
                $collegeStats[$year]['recYds'] = $rowChildren->item(7)->nodeValue;
                $collegeStats[$year]['recAvg'] = $rowChildren->item(8)->nodeValue;
                $collegeStats[$year]['recTds'] = $rowChildren->item(9)->nodeValue;
                $collegeStats[$year]['ydsDominator'] = (round($collegeStats[$year]['recYds'] / $totals['yds'], 4)) * 100;
                $collegeStats[$year]['recDominator'] = (round($collegeStats[$year]['receptions'] / $totals['recs'], 4)) * 100;
                $collegeStats[$year]['tdDominator'] = (round($collegeStats[$year]['recTds'] / $totals['tds'], 4)) * 100;
            }
            // $result is a DOMElement
        }

        $collegeJson = json_encode($collegeStats);

        $sql = new Sql($this->db);
        $update = $sql->update('players');
        $update->set(["collegeStats" => $collegeJson]);
        $update->where(['id = ?' => $wr['id']]);
        $stmt   = $sql->prepareStatementForSqlObject($update);
        $playerUpdated = $stmt->execute();
        return true;
    }


}