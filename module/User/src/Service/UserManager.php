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
use Laminas\Crypt\Password\Bcrypt;
use Exception;
use Laminas\Math\Rand;
use Laminas\Mail;

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

    public function getUsers()
    {
        return $this->repository->getAllUsers();
    }

    public function findUserById($id)
    {
        return $this->repository->getUserById($id);
    }

    public function findUserByEmail($email)
    {
        return $this->repository->getUserByEmail($email);
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
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);

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
     * This method updates data of an existing user.
     */
    public function updateUser($user, $data)
    {
        // Do not allow to change user email if another user with such email already exits.
        if($user->getEmail()!=$data['email'] && $this->checkUserExists($data['email'])) {
            throw new \Exception("Another user with email address " . $data['email'] . " already exists");
        }

        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setStatus($data['status']);

        // Apply changes to database.
        $this->command->updateUser($user);
        return true;
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

    /**
     * Generates a password reset token for the user. This token is then stored in database and
     * sent to the user's E-mail address. When the user clicks the link in E-mail message, he is
     * directed to the Set Password page.
     */
    public function generatePasswordResetToken($user)
    {
        if ($user->getStatus() != User::STATUS_ACTIVE) {
            throw new \Exception('Cannot generate password reset token for inactive user ' . $user->getEmail());
        }

        // Generate a token.
        $token = Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz', true);

        // Encrypt the token before storing it in DB.
        $bcrypt = new Bcrypt();
        $tokenHash = $bcrypt->create($token);

        // Save token to DB
        $user->setPasswordResetToken($tokenHash);

        // Save token creation date to DB.
        $currentDate = date('Y-m-d H:i:s');
        $user->setPasswordResetTokenCreationDate($currentDate);

        // Apply changes to DB.
        $this->command->save($user);

//        // Send an email to user.
//        $subject = 'Password Reset';
//
//        $httpHost = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
//        $passwordResetUrl = 'http://' . $httpHost . '/set-password?token=' . $token . "&email=" . $user->getEmail();
//
//        // Produce HTML of password reset email
//        $bodyHtml = $this->viewRenderer->render(
//            'user/email/reset-password-email',
//            [
//                'passwordResetUrl' => $passwordResetUrl,
//            ]);
//
//        $html = new MimePart($bodyHtml);
//        $html->type = "text/html";
//
//        $body = new MimeMessage();
//        $body->addPart($html);
//
//        $mail = new Mail\Message();
//        $mail->setEncoding('UTF-8');
//        $mail->setBody($body);
//        $mail->setFrom('no-reply@example.com', 'User Demo');
//        $mail->addTo($user->getEmail(), $user->getFullName());
//        $mail->setSubject($subject);
//
//        // Setup SMTP transport
//        $transport = new SmtpTransport();
//        $options   = new SmtpOptions($this->config['smtp']);
//        $transport->setOptions($options);
//        $transport->send($mail);
    }

    public function validatePasswordResetToken($email, $passwordResetToken)
    {
        // Find user by email.
        $user = $this->entityManager->getRepository(User::class)
            ->findOneByEmail($email);

        if($user==null || $user->getStatus() != User::STATUS_ACTIVE) {
            return false;
        }

        // Check that token hash matches the token hash in our DB.
        $bcrypt = new Bcrypt();
        $tokenHash = $user->getPasswordResetToken();

        if (!$bcrypt->verify($passwordResetToken, $tokenHash)) {
            return false; // mismatch
        }

        // Check that token was created not too long ago.
        $tokenCreationDate = $user->getPasswordResetTokenCreationDate();
        $tokenCreationDate = strtotime($tokenCreationDate);

        $currentDate = strtotime('now');

        if ($currentDate - $tokenCreationDate > 24*60*60) {
            return false; // expired
        }

        return true;
    }

    /**
     * This method sets new password by password reset token.
     */
    public function setNewPasswordByToken($email, $passwordResetToken, $newPassword)
    {
        if (!$this->validatePasswordResetToken($email, $passwordResetToken)) {
            return false;
        }

        // Find user with the given email.
        $user = $this->repository->findUserByEmail($email);

        if ($user==null || $user->getStatus() != User::STATUS_ACTIVE) {
            return false;
        }

        // Set new password for user
        $bcrypt = new Bcrypt();
        $passwordHash = $bcrypt->create($newPassword);
        $user->setPassword($passwordHash);

        // Remove password reset token
        $user->setPasswordResetToken(null);
        $user->setPasswordResetTokenCreationDate(null);

        $this->command->save($user);

        return true;
    }

    /**
     * This method is used to change the password for the given user. To change the password,
     * one must know the old password.
     */
    public function changePassword($user, $data)
    {
        $oldPassword = $data['old_password'];

        // Check that old password is correct
        if (!$this->validatePassword($user, $oldPassword)) {
            return false;
        }

        $newPassword = $data['new_password'];

        // Check password length
        if (strlen($newPassword)<6 || strlen($newPassword)>64) {
            return false;
        }

        // Set new password for user
        $bcrypt = new Bcrypt();
        $passwordHash = $bcrypt->create($newPassword);
        $user->setPassword($passwordHash);

        // Apply changes
        $this->command->save($user);
        return true;
    }



}