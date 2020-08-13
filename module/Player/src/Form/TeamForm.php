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
        $coachFields = ["headCoach", "headCoachType", "offCoordinator", "defCoordinator"];
        foreach ($coachFields as $key) {
            $newField = new Element($key);
            $newField->setLabel($key);
            $newField->setLabelAttributes(['class' => 'form-group col m3']);
            $coaches->add($newField);
//            if ($team != null && array_key_exists($key, $info)) {
//                $newField->setValue($info[$key]);
//            }
        }
        $this->add($coaches);

        $personnel = [
            "1-1",
            "1-2",
            "2-1",
            "2-2",
            "1-3",
            "1-0",
            "2-0",
            "0-2",
            "0-1",
        ];

        $scheme = new Fieldset('scheme');
        $schemeFields = [
            "passRatio",
            "runRatio",
            "shotgonRatio",
            "underCenterRatio",
            "screenRatio",
            "zoneRunRate",
            "gapRunRate",
            "playsPerGame",
            "passPerGame",
            "runPerGame",
            "drivesPerGame",
            "outsideThrows",
            "insideThrows",
            "throwsUnder15",
            "throwsUnder25",
            "throwsOver25",
            "qbSkill",
            "qbType",
            "qbMobile",
            "olPowerRun",
            "olZoneRun",
            "olInside",
            "olOutside",
            "fbRun",
            "wr1Type",
            "wr2Type",
            "wr3Type",
            "wr1Vol",
            "wr2Vol",
            "wr3Vol",
            "teType",
            "teVol",
            "rb1Type",
            "rb1Vol",
            "rb2Type",
            "rb2Vol",
            "defRun",
            "defPass",
            "defPush",
            "defRush",
            "lbCoverage",
            "cbCoverage",
            "safetyCoverage"
        ];
        foreach ($schemeFields as $key) {
            $newField = new Element($key);
            $newField->setLabel($key);
            $newField->setLabelAttributes(['class' => 'form-group col m2']);
            $scheme->add($newField);
//            if ($info != null && array_key_exists($key, $info)) {
//                $newField->setValue($info[$key]);
//            }
        }
        $this->add($scheme);

        $support = new Fieldset('support');

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