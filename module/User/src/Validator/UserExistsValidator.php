<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/9/19
 * Time: 3:56 PM
 */

namespace User\Validator;


use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

class UserExistsValidator extends AbstractValidator
{

    protected $options = array(
        'entityManager' => null,
        'user' => null
    );

    // Validation failure message IDs.
    const NOT_SCALAR  = 'notScalar';
    const USER_EXISTS = 'userExists';

    /**
     * Validation failure messages.
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_SCALAR  => "The email must be a scalar value",
        self::USER_EXISTS  => "Another user with such an email already exists"
    );

    /**
     * Constructor.
     */
    public function __construct($options = null)
    {
        // Set filter options (if provided).
        if(is_array($options)) {
            if(isset($options['entityManager']))
                $this->options['entityManager'] = $options['entityManager'];
            if(isset($options['user']))
                $this->options['user'] = $options['user'];
        }

        // Call the parent class constructor
        parent::__construct($options);
    }


    /**
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if(!is_scalar($value)) {
            $this->error(self::NOT_SCALAR);
            return false;
        }

        // Get Doctrine entity manager.
        $userManager = $this->options['entityManager'];

        $user = $userManager->findUserByEmail($value);

        if($this->options['user']==null) {
            $isValid = ($user==null);
        } else {
            if($this->options['user']->getEmail()!=$value && $user!=null)
                $isValid = false;
            else
                $isValid = true;
        }

        // If there were an error, set error message.
        if(!$isValid) {
            $this->error(self::USER_EXISTS);
        }

        // Return validation result.
        return $isValid;
    }

}