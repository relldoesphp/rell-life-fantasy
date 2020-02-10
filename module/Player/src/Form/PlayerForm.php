<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/29/19
 * Time: 1:07 AM
 */

namespace Player\Form;

use Player\Model\Player\Player;
use Zend\Captcha;
use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class PlayerForm extends Form
{
    public function __construct($name = null)
    {
        // We will ignore the name provided to the constructor
        parent::__construct('player');

        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);
        $this->add([
            'name' => 'first_name',
            'type' => 'text',
            'options' => [
                'label' => 'First Name',
            ],
        ]);
        $this->add([
            'name' => 'last_name',
            'type' => 'text',
            'options' => [
                'label' => 'Last Name',
            ],
        ]);

        $this->add([
            'type' => Element\Select::class,
            'name' => 'position',
            'options' => [
                'label' => 'Position',
                'value_options' => [
                    'QB' => 'QB',
                    'RB' => 'RB',
                    'WR' => 'WR',
                    'TE' => 'TE',
                    'OT' => 'OT',
                    "G" => 'G',
                    'C' => 'C',
                    'DE' => 'DE',
                    'DT' => 'DT',
                    'NT' => 'NT',
                    'OLB' => 'OLB',
                    'ILB' => 'ILB',
                    'CB' => 'CB',
                    'FS' => 'FS',
                    'SS' => 'SS'
                ],
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'team',
            'options' => [
                'label' => 'Team',
            ],
        ]);

        $this->add([
           'type' => 'textarea',
           'name' => 'player_info',
            'options' => [
                'label' => 'player_info',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'team_info',
            'options' => [
                'label' => 'team_info',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'api_info',
            'options' => [
                'label' => 'api_info',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'injury_info',
            'options' => [
                'label' => 'injury_info',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'fortyTime',
            'options' => [
                'label' => '40 time',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'benchPress',
            'options' => [
                'label' => 'bench',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'shuttle',
            'options' => [
                'label' => 'Shuttle',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'cone',
            'options' => [
                'label' => '3 cone',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'broadJump',
            'options' => [
                'label' => 'broadJump',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'verticalJump',
            'options' => [
                'label' => 'verticalJump',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'metrics',
            'options' => [
                'label' => 'metrics',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'metrics',
            'options' => [
                'label' => 'metrics',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'percentiles',
            'options' => [
                'label' => 'percentiles',
            ],
        ]);

        $this->add([
            'type' => 'textarea',
            'name' => 'college_stats',
            'options' => [
                'label' => 'college_stats',
                'rows' => '5',
            ],
        ]);

        $this->get('college_stats')->setValue('{"2014": {"recs": "", "year": "2014", "class": "", "games": "", "recAvg": "", "recTds": "", "recYds": "", "rushes": "", "totals": {"tds": "", "yds": "", "recs": "", "returnTds": "", "returnYds": ""}, "college": "", "rushAvg": "", "rushTds": "", "rushYds": "", "position": "", "conference": ""}}');

        $this->add([
            'type' => 'text',
            'name' => 'image1',
            'options' => [
                'label' => 'image1',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'image2',
            'options' => [
                'label' => 'image2',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'image3',
            'options' => [
                'label' => 'image3',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'image4',
            'options' => [
                'label' => 'image4',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'image5',
            'options' => [
                'label' => 'image5',
            ],
        ]);


        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Go',
                'id' => 'submitbutton',
            ],
        ]);
    }

    public function addFieldsets(Player $player){
        $player->decodeJson();
        $metrics = $player->getMetrics();
        $info = $player->getPlayerInfo();
        $team = $player->getTeamInfo();
        $apiInfo = $player->getApiInfo();

        $body = new Fieldset('body');
        $bodyFields = ["bmi", "arms", "armsInches", "weight", "age", "height", "heightInches", "college", "draft_pick", "draft_year"];
        foreach ($bodyFields as $key) {
            $newField = new Element($key);
            $newField->setLabel($key);
            $newField->setLabelAttributes(['class' => 'form-group col m2']);
            $body->add($newField);
            if ($info != null && array_key_exists($key, $info)) {
                $newField->setValue($info[$key]);
            }
        }
        $this->add($body);

        $teamFieldset = new Fieldset('teamFieldset');
        foreach ($team as $key => $value) {
            $newField = new Element($key);
            $newField->setLabel($key);
            $newField->setLabelAttributes(['class' => 'form-group col m2']);
            $teamFieldset->add($newField);
            if (getType($value) == "string" || getType($value) == "integer") {
                $teamFieldset->get($key)->setValue($value);
            }
        }
        $this->add($teamFieldset);

        $api = new Fieldset('apiFieldset');
        foreach ($apiInfo as $key => $value) {
            $newField = new Element($key);
            $newField->setLabel($key);
            $newField->setLabelAttributes(['class' => 'form-group col m2']);
            $api->add($newField);
            if (getType($value) == "string") {
                $api->get($key)->setValue($value);
            }
        }
        $this->add($api);

        $metricsFieldset = new Fieldset('metricsFieldset');
        foreach ($metrics as $key => $value) {
            $newField = new Element($key);
            $newField->setLabel($key);
            $newField->setLabelAttributes(['class' => 'form-group col m2']);
            $metricsFieldset->add($newField);
            if (getType($value) == "string") {
                $metricsFieldset->get($key)->setValue($value);
                $combineFields = ["benchPress", "fortyTime", "shuttle", "cone", "verticalJump", "broadJump"];
                if (in_array($key, $combineFields)) {
                    $this->get($key)->setValue($value);
                }
            }
        }

        $this->add($metricsFieldset);

        $i = 0;
        $college = new Fieldset("college");
        while ($i < 4) {
            $collegeFieldset = new Fieldset("college-{$i}");
            $collegeStats = array_values($player->getCollegeStats());

            switch ($player->getPosition()) {
                case "WR":
                case "TE":
                    $fields = ["year","college","class","conference","games","recs","recYds","recAvg","recTds","rushes","rushAvg","rushTds","rushYds"];
                    foreach ($fields as $field) {
                        $newField = new Element($field);
                        $newField->setLabel($field);
                        $newField->setLabelAttributes(['class' => 'form-group col m1']);
                        if ($collegeStats != null && array_key_exists($i, $collegeStats) && array_key_exists($field, $collegeStats[$i])) {
                            $newField->setValue($collegeStats[$i][$field]);
                        }
                        $collegeFieldset->add($newField);
                    }

                    //return fieldset
                    $returnStats = new Fieldset("returnStats");
                    $returnStats->setLabel("Return Stats");
                    $returnFields = ["kickAvg", "kickYds", "kickTds", "puntAvg", "puntYds", "puntTds"];
                    foreach ($returnFields as $field) {
                        $newField = new Element($field);
                        $newField->setLabel($field);
                        $newField->setLabelAttributes(['class' => 'form-group col m2']);
                        $returnStats->add($newField);
                        if ($collegeStats != null
                            && array_key_exists($i, $collegeStats)
                            && array_key_exists('returnStats', $collegeStats[$i])
                            && array_key_exists($field, $collegeStats[$i]['returnStats'])
                        ) {
                            $newField->setValue($collegeStats[$i]['returnStats'][$field]);
                        }
                    }
                    $collegeFieldset->add($returnStats);

                    //total fieldset
                    $teamTotal = new Fieldset("totals");
                    $teamTotal->setLabel("totals");
                    $totalFields = ["tds","yds","recs","returnTds","returnYds"];
                    foreach ($totalFields as $field) {
                        $newField = new Element($field);
                        $newField->setLabel($field);
                        $newField->setLabelAttributes(['class' => 'form-group col m2']);
                        $teamTotal->add($newField);
                        if ($collegeStats != null && array_key_exists($i, $collegeStats) && array_key_exists($field, $collegeStats[$i]['totals'])) {
                            $newField->setValue($collegeStats[$i]['totals'][$field]);
                        }
                    }
                    $collegeFieldset->add($teamTotal);
                    $college->add($collegeFieldset);
                    break;
                case "RB":
                    $fields = ["year","college","class","conference","games","recs","recYds","recAvg","recTds","rushes","rushAvg","rushTds","rushYds"];
                    foreach ($fields as $field) {
                        $newField = new Element($field);
                        $newField->setLabel($field);
                        $newField->setLabelAttributes(['class' => 'form-group col m1']);
                        if ($collegeStats != null && array_key_exists($i, $collegeStats) && array_key_exists($field, $collegeStats[$i])) {
                            $newField->setValue($collegeStats[$i][$field]);
                        }
                        $collegeFieldset->add($newField);
                    }

                    //return fieldset
                    $returnStats = new Fieldset("returnStats");
                    $returnStats->setLabel("Return Stats");
                    $returnFields = ["kickAvg", "kickYds", "kickTds", "puntAvg", "puntYds", "puntTds"];
                    foreach ($returnFields as $field) {
                        $newField = new Element($field);
                        $newField->setLabel($field);
                        $newField->setLabelAttributes(['class' => 'form-group col m2']);
                        $returnStats->add($newField);
                        if ($collegeStats != null
                            && array_key_exists($i, $collegeStats)
                            && array_key_exists('returnStats', $collegeStats[$i])
                            && array_key_exists($field, $collegeStats[$i]['returnStats'])
                        ) {
                            $newField->setValue($collegeStats[$i]['returnStats'][$field]);
                        }
                    }
                    $collegeFieldset->add($returnStats);

                    //total fieldset
                    $teamTotal = new Fieldset("totals");
                    $teamTotal->setLabel("totals");
                    $totalFields = ["tds","yds","recs","returnTds","returnYds"];
                    foreach ($totalFields as $field) {
                        $newField = new Element($field);
                        $newField->setLabel($field);
                        $newField->setLabelAttributes(['class' => 'form-group col m2']);
                        $teamTotal->add($newField);
                        if ($collegeStats != null && array_key_exists($i, $collegeStats) && array_key_exists($field, $collegeStats[$i]['totals'])) {
                            $newField->setValue($collegeStats[$i]['totals'][$field]);
                        }
                    }
                    $collegeFieldset->add($teamTotal);
                    $college->add($collegeFieldset);
                    break;
                case "QB":
                default:
            }
            $i++;
        }
        $this->add($college);

    }

    public function updatePlayer(Player $player, $data) {
        if (empty($data)) {
            return;
        }

        $player->decodeJson();
        //get combine stats
        $metrics = $player->getMetrics();
        $metrics['fortyTIme'] = $data["fortyTime"];
        $metrics['verticalJump'] = $data["verticalJump"];
        $metrics['broadJump'] = $data["broadJump"];
        $metrics['shuttle'] = $data["shuttle"];
        $metrics['cone'] = $data["cone"];
        $metrics['benchPress'] = $data["benchPress"];

        //get body stats
        $playerInfo = $player->getPlayerInfo();
        $playerInfo["bmi"] = $data["body"]["bmi"];
        $playerInfo["arms"] = $data["body"]["arms"];
        $playerInfo["armsInches"] = $data["body"]["armsInches"];
     //   $playerInfo["hands"] = $data["body"]["hands"];
        $playerInfo["height"] = $data["body"]["height"];
        $playerInfo["heightInches"] = $data["body"]["heightInches"];
        $playerInfo["college"] = $data["body"]["college"];
        $playerInfo["weight"] = $data["body"]["weight"];
        $palyerInfo["age"] = $data["body"]["age"];

        //college stats
        $collegeStats = $player->getCollegeStats();
        foreach ($data["college"] as $key => $stats) {
            if (!empty($stats["year"])) {
                $collegeStats[$stats["year"]] = $stats;
            }
        }

        $player->setCollegeStats($collegeStats);
        $player->setPlayerInfo($playerInfo);
        $player->setMetrics($metrics);

        return $player;










    }
}