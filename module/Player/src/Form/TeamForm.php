<?php


namespace Player\Form;

use Player\Model\Team\Team;
use Laminas\Captcha;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

class TeamForm extends Form
{

    public function __construct(Team $team)
    {
        parent::__construct('team');

        $this->add([
            'name' => 'id',
            'type' => 'hidden',
            'value' => $team->getId()
        ]);
        $this->add([
            'name' => 'team',
            'type' => 'text',
            'options' => [
                'label' => 'team',
            ],
            'value' => $team->getTeam()
        ]);
        $this->add([
            'name' => 'city',
            'type' => 'text',
            'options' => [
                'label' => 'city',
            ],
            'value' => $team->getCity()
        ]);
        $this->add([
            'name' => 'name',
            'type' => 'text',
            'options' => [
                'label' => 'name',
            ],
            'value' => $team->getName()
        ]);

        $coaches = new Fieldset('coaches');
        $coachFields = ["headCoach", "headCoachType", "offCoordinator", "defCoordinator"];
        $coachInfo = $team->getCoaches();
        foreach ($coachFields as $key) {
            $newField = new Element($key);
            $newField->setLabel($key);
            $newField->setLabelAttributes(['class' => 'form-group col m3']);
            $coaches->add($newField);
            if ($team != null && array_key_exists($key, $coachInfo)) {
                $newField->setValue($coachInfo[$key]);
            }
        }
        $this->add($coaches);

        $personnel = new Fieldset('personnel');
        $personnelFields = [
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
        $personnelInfo = $team->getPersonnel();
        foreach ($personnelFields as $key) {
            $newField = new Element($key);
            $newField->setLabel($key);
            $newField->setLabelAttributes(['class' => 'form-group col m2']);
            $personnel->add($newField);
            if ($personnelInfo != null && array_key_exists($key, $personnelInfo)) {
                $newField->setValue($personnelInfo[$key]);
            }
        }
        $this->add($personnel);

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
        ];
        $schemeInfo = $team->getScheme();
        foreach ($schemeFields as $key) {
            $newField = new Element($key);
            $newField->setLabel($key);
            $newField->setLabelAttributes(['class' => 'form-group col m2']);
            $scheme->add($newField);
            if ($schemeInfo != null && array_key_exists($key, $schemeInfo)) {
                $newField->setValue($schemeInfo[$key]);
            }
        }
        $this->add($scheme);

        $teamVolume = new Fieldset('teamVolume');
        $teamVolumeFields = [
            "wr1Vol" => ['low', 'low-mid', 'average', 'mid-high', 'high'],
            "wr2Vol" => ['low', 'low-mid', 'average', 'mid-high', 'high'],
            "wr3Vol" => ['low', 'low-mid', 'average', 'mid-high', 'high'],
            "teVol" => ['low', 'low-mid', 'average', 'mid-high', 'high'],
            "rb1PassVol" => ['low', 'low-mid', 'average', 'mid-high', 'high'],
            "rb2PassVol" => ['low', 'low-mid', 'average', 'mid-high', 'high'],
            "rb1RunVol" => ['low', 'low-mid', 'average', 'mid-high', 'high'],
            "rb2RunVol" => ['low', 'low-mid', 'average', 'mid-high', 'high'],
        ];
        $volumeInfo = $team->getVolume();
        foreach ($teamVolumeFields as $key => $value) {
            if (is_array($value)) {
                $newField = new Element\Select($key);
                $newField->setLabel($key);
                $newField->setValueOptions($value);
                $newField->setLabelAttributes(['class' => 'form-group col m2']);
            } else {
                $newField = new Element($key);
                $newField->setLabel($key);
                $newField->setLabelAttributes(['class' => 'form-group col m2']);
            }

            $teamVolume->add($newField);
            if ($volumeInfo != null && array_key_exists($key, $volumeInfo)) {
                $newField->setValue($volumeInfo[$key]);
            }
        }
        $this->add($teamVolume);

        $positionRoles = new Fieldset('positionRoles');
        $positionRolesFields = [
            "qbType" => ['game-manager', 'mobile', 'pocket', 'gun-slinger'],
            "wr1Type" => ['alpha', 'deep', 'slot', 'gadget', 'possession', 'field-strecther'],
            "wr2Type" => ['alpha', 'deep', 'slot', 'gadget', 'possession', 'field-strecther'],
            "wr3Type" => ['alpha', 'deep', 'slot', 'gadget', 'possession', 'field-strecther'],
            "te1Type" => ['alpha', 'seam', 'underneath', 'blocker'],
            "te2Type" => ['alpha', 'seam', 'underneath', 'blocker'],
            "rb1Type" => ['grinder', 'scat', '1A', '1B', 'alpha', 'balanced'],
            "rb2Type" => ['grinder', 'scat', '1A', '1B', 'alpha', 'balanced'],
        ];
        $rolesInfo = $team->getRoles();
        foreach ($positionRolesFields as $key => $value) {
            if (is_array($value)) {
                $newField = new Element\Select($key);
                $newField->setLabel($key);
                $newField->setValueOptions($value);
                $newField->setLabelAttributes(['class' => 'form-group col m2']);
            } else {
                $newField = new Element($key);
                $newField->setLabel($key);
                $newField->setLabelAttributes(['class' => 'form-group col m2']);
            }

            $positionRoles->add($newField);
            if ($rolesInfo != null && array_key_exists($key, $rolesInfo)) {
                $newField->setValue($rolesInfo[$key]);
            }
        }
        $this->add($positionRoles);

        $teamRatings = new Fieldset('teamRatings');
        $teamRatingFields = [
            "qbSkill" => [0,1,2,3,4,5],
            "qbMobile" => [0,1,2,3,4,5],
            "olPowerRun" => "",
            "olZoneRun" => "",
            "olInside" => "",
            "olOutside" => "",
            "fbRun" => [0,1],
            "defRun" => [0,1,2,3,4,5],
            "defPass" => [0,1,2,3,4,5],
            "defPush" => [0,1,2,3,4,5],
            "defRush" => [0,1,2,3,4,5],
            "lbCoverage" => [0,1,2,3,4,5],
            "cbCoverage" => [0,1,2,3,4,5],
            "safetyCoverage" => [0,1,2,3,4,5]
        ];
        $ratingsInfo = $team->getRatings();
        foreach ($teamRatingFields as $key => $value) {
            if (is_array($value)) {
                $newField = new Element\Select($key);
                $newField->setLabel($key);
                $newField->setValueOptions($value);
                $newField->setLabelAttributes(['class' => 'form-group col m2']);
            } else {
                $newField = new Element($key);
                $newField->setLabel($key);
                $newField->setLabelAttributes(['class' => 'form-group col m2']);
            }

            $teamRatings->add($newField);
            if ($ratingsInfo != null && array_key_exists($key, $ratingsInfo)) {
                $newField->setValue($ratingsInfo[$key]);
            }
        }
        $this->add($teamRatings);
//
//        $support = new Fieldset('support');
//        $supportFields = [
//
//        ];
//
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