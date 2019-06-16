<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 4/5/19
 * Time: 11:34 PM
 */

namespace Player\Model;

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

class SqlTeCommand extends SqlPlayerAbstract
{
    public function calculateSpecialScores()
    {

    }

    public function calculateSpecialPercentages()
    {

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