<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/29/19
 * Time: 1:07 AM
 */

namespace Player\Form;

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
            'name' => '3cone',
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
                'rows'        => '5',
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
}