<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/22/19
 * Time: 11:06 PM
 */

namespace User\Service;

use User\Model\User\CommandInterface;
use User\Model\User\RepositoryInterface;
use User\Model\User\User;
use User\Model\User\Sql;
use Zend\Crypt\Password\Bcrypt;
use Exception;

class UserManager
{

    private $repository;

    private $command;

    public function __construct(
        RepositoryInterface $repository,
        CommandInterface $command, $viewRender, $config)
    {
        $this->repository = $repository;
        $this->command = $command;
    }

    /**
     * This method adds a new user.
     */
    public function addUser($data)
    {
        // Do not allow several users with the same email address.
        if($this->checkUserExists($data['email'])) {
            throw new Exception("User with email address " . $data['$email'] . " already exists");
        }

        // Create new User entity.
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);

        // Encrypt password and store the password in encrypted state.
        $bcrypt = new Bcrypt();
        $passwordHash = $bcrypt->create($data['password']);
        $user->setPassword($passwordHash);

        $user->setStatus($data['status']);

        $currentDate = date('Y-m-d H:i:s');
        $user->setDateCreated($currentDate);

        // Add the entity to the entity manager.
        $this->command->addUser($user);

        return $user;
    }

    /**
     * Checks that the given password is correct.
     */
    public function validatePassword($user, $password)
    {
        $bcrypt = new Bcrypt();
        $passwordHash = $user->getPassword();

        if ($bcrypt->verify($password, $passwordHash)) {
            return true;
        }

        return false;
    }

    public function checkUserExists($email) {
        return $this->repository->getUserByEmail($email);
    }

    public function createAdminUserIfNotExists()
    {
        $user = $this->repository->getAllUsers();
        if ($user == null) {
            $user = new User();
            $user->setEmail('admin@example.com');
            $user->setFullName('Admin');
            $bcrypt = new Bcrypt();
            $passwordHash = $bcrypt->create('Secur1ty');
            $user->setPassword($passwordHash);
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setDateCreated(date('Y-m-d H:i:s'));

            $this->command->save($user);
        }
    }
}