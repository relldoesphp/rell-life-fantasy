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
            'type' => 'textarea',
            'name' => 'images',
            'options' => [
                'label' => 'images',
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