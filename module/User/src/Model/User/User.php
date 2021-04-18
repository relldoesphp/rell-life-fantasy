<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/22/19
 * Time: 3:15 PM
 */

namespace User\Model\User;

use Laminas\Json\Json;

class User
{
    // User status constants.
    const STATUS_ACTIVE       = 1; // Active user.
    const STATUS_RETIRED      = 2; // Retired user.

    protected $id;
    protected $email;
    protected $firstName;
    protected $lastName;
    protected $password;
    protected $status;
    protected $level;
    protected $passwordResetToken;
    protected $patreon_id;
    protected $patreon_image;
    protected $patreon_token;
    protected $patreon_attributes;
    protected $patreon_membership;
    private $roles;

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
    protected $passwordResetTokenCreationDate;

    /**
     * Returns user ID.
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets user ID.
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns email.
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets email.
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Returns full name.
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Sets full name.
     * @param string $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * Returns status.
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Returns possible statuses as array.
     * @return array
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_RETIRED => 'Retired'
        ];
    }

    /**
     * Returns user status as string.
     * @return string
     */
    public function getStatusAsString()
    {
        $list = self::getStatusList();
        if (isset($list[$this->status]))
            return $list[$this->status];

        return 'Unknown';
    }

    /**
     * Sets status.
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Returns password.
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets password.
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Returns the date of user creation.
     * @return string
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Sets the date when this user was created.
     * @param string $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * Returns password reset token.
     * @return string
     */
    public function getResetPasswordToken()
    {
        return $this->passwordResetToken;
    }

    /**
     * Sets password reset token.
     * @param string $token
     */
    public function setPasswordResetToken($token)
    {
        $this->passwordResetToken = $token;
    }

    /**
     * Returns password reset token's creation date.
     * @return string
     */
    public function getPasswordResetTokenCreationDate()
    {
        return $this->passwordResetTokenCreationDate;
    }

    /**
     * Sets password reset token's creation date.
     * @param string $date
     */
    public function setPasswordResetTokenCreationDate($date)
    {
        $this->passwordResetTokenCreationDate = $date;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }



    /**
     * Returns the array of roles assigned to this user.
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the string of assigned role names.
     */
    public function getRolesAsString()
    {
        $roleList = '';

        $count = count($this->roles);
        $i = 0;
        foreach ($this->roles as $role) {
            $roleList .= $role->getName();
            if ($i<$count-1)
                $roleList .= ', ';
            $i++;
        }

        return $roleList;
    }

    /**
     * Assigns a role to user.
     */
    public function addRole($role)
    {
        $this->roles->add($role);
    }

    /**
     * @return mixed
     */
    public function getPatreonId()
    {
        return $this->patreon_id;
    }

    /**
     * @param mixed $patreon_id
     */
    public function setPatreonId($patreon_id)
    {
        $this->patreon_id = $patreon_id;
    }

    /**
     * @return mixed
     */
    public function getPatreonImage()
    {
        return $this->patreon_image;
    }

    /**
     * @param mixed $patreon_image
     */
    public function setPatreonImage($patreon_image)
    {
        $this->patreon_image = $patreon_image;
    }

    /**
     * @return mixed
     */
    public function getPatreonToken()
    {
        return $this->patreon_token;
    }

    /**
     * @param mixed $patreon_token
     */
    public function setPatreonToken($patreon_token)
    {
        $this->patreon_token = $patreon_token;
    }

    /**
     * @return mixed
     */
    public function getPatreonAttributes()
    {
        return $this->patreon_attributes;
    }

    /**
     * @param mixed $patreon_attributes
     */
    public function setPatreonAttributes($patreon_attributes)
    {
        $this->patreon_attributes = $patreon_attributes;
    }

    /**
     * @return mixed
     */
    public function getPatreonMembership()
    {
        return $this->patreon_membership;
    }

    /**
     * @param mixed $patreon_membership
     */
    public function setPatreonMembership($patreon_membership)
    {
        $this->patreon_membership = $patreon_membership;
    }

    public function encodeJson()
    {
        if (is_array($this->patreon_token)) {
            $this->patreon_token = Json::encode($this->patreon_token);
        }

        if (is_array($this->patreon_attributes)) {
            $this->patreon_attributes = Json::encode($this->patreon_attributes);
        }

        if (is_array($this->patreon_membership)) {
            $this->patreon_membership = Json::encode($this->patreon_membership);
        }
    }

    public function decodeJson()
    {
        if (!is_array($this->patreon_token)) {
            $this->patreon_token =  (!empty($this->patreon_token)) ? Json::decode($this->patreon_token, 1) : [];
        }

        if (!is_array($this->patreon_attributes)) {
            $this->patreon_attributes = (!empty($this->patreon_attributes)) ? Json::decode($this->patreon_attributes, 1): [];
        }

        if (!is_array($this->patreon_membership)) {
            $this->patreon_membership = (!empty($this->patreon_membership)) ? Json::decode($this->patreon_membership, 1) : [];
        }
    }
}