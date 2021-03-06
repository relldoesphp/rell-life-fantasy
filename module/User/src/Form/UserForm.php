<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/2/19
 * Time: 4:42 PM
 */

namespace User\Form;

use Laminas\Form\Form;
use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilter;
use User\Validator\UserExistsValidator;


class UserForm extends Form
{

    /**
     * Scenario ('create' or 'update').
     * @var string
     */
    private $scenario;

    private $userManager = null;

    private $user = null;

    public function __construct($scenario = 'create', $userManager = null, $user = null)
    {
        // Define form name
        parent::__construct('user-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        // Save parameters for internal use.
        $this->scenario = $scenario;
        $this->userManager = $userManager;
        $this->user = $user;

        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements()
    {
        // Add "email" field
        $this->add([
            'type'  => 'text',
            'name' => 'email',
            'options' => [
                'label' => 'E-mail',
            ],
        ]);

        // Add "full_name" field
        $this->add([
            'type'  => 'text',
            'name' => 'firstName',
            'options' => [
                'label' => 'First Name',
            ],
        ]);

        // Add "full_name" field
        $this->add([
            'type'  => 'text',
            'name' => 'lastName',
            'options' => [
                'label' => 'Last Name',
            ],
        ]);

        if ($this->scenario == 'create') {

            // Add "password" field
            $this->add([
                'type'  => 'password',
                'name' => 'password',
                'options' => [
                    'label' => 'Password',
                ],
            ]);

            // Add "confirm_password" field
            $this->add([
                'type'  => 'password',
                'name' => 'confirm_password',
                'options' => [
                    'label' => 'Confirm password',
                ],
            ]);
        }

        // Add "status" field
        $this->add([
            'type'  => 'select',
            'name' => 'status',
            'options' => [
                'label' => 'Status',
                'value_options' => [
                    1 => 'Active',
                    2 => 'Retired',
                ]
            ],
        ]);

        // Add the Submit button
        $this->add([
            'type'  => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Create'
            ],
        ]);
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter()
    {
        // Create main input filter
        $inputFilter = $this->getInputFilter();

        // Add input for "email" field
        $inputFilter->add([
            'name'     => 'email',
            'required' => true,
            'filters'  => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
                    'options' => [
                        'min' => 1,
                        'max' => 128
                    ],
                ],
                [
                    'name' => 'EmailAddress',
                    'options' => [
                        'allow' => \Laminas\Validator\Hostname::ALLOW_DNS,
                        'useMxCheck'    => false,
                    ],
                ],
                [
                    'name' => UserExistsValidator::class,
                    'options' => [
                        'entityManager' => $this->userManager,
                        'user' => $this->user
                    ],
                ],
            ],
        ]);

        // Add input for "full_name" field
        $inputFilter->add([
            'name'     => 'firstName',
            'required' => true,
            'filters'  => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name'    => 'StringLength',
                    'options' => [
                        'min' => 1,
                        'max' => 512
                    ],
                ],
            ],
        ]);

        if ($this->scenario == 'create') {

            // Add input for "password" field
            $inputFilter->add([
                'name'     => 'password',
                'required' => true,
                'filters'  => [
                ],
                'validators' => [
                    [
                        'name'    => 'StringLength',
                        'options' => [
                            'min' => 6,
                            'max' => 64
                        ],
                    ],
                ],
            ]);

            // Add input for "confirm_password" field
            $inputFilter->add([
                'name'     => 'confirm_password',
                'required' => true,
                'filters'  => [
                ],
                'validators' => [
                    [
                        'name'    => 'Identical',
                        'options' => [
                            'token' => 'password',
                        ],
                    ],
                ],
            ]);
        }

        // Add input for "status" field
        $inputFilter->add([
            'name'     => 'status',
            'required' => true,
            'filters'  => [
                ['name' => 'ToInt'],
            ],
            'validators' => [
                ['name'=>'InArray', 'options'=>['haystack'=>[1, 2]]]
            ],
        ]);
    }
}