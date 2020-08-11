<?php


namespace Player\Form;

use Player\Model\Team;
use Laminas\Captcha;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

class TeamForm extends Form
{

    public function __construct($name = null)
    {
        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);
        $this->add([
            'name' => 'team',
            'type' => 'text',
            'options' => [
                'label' => 'team',
            ],
        ]);
        $this->add([
            'name' => 'city',
            'type' => 'text',
            'options' => [
                'label' => 'city',
            ],
        ]);
        $this->add([
            'name' => 'name',
            'type' => 'text',
            'options' => [
                'label' => 'name',
            ],
        ]);

        $coaches = new Fieldset('coaches');

        $scheme = new Fieldset('scheme');

        $support = new Fieldset('coaches');

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