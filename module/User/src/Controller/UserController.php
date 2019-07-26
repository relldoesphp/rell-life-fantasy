<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/22/19
 * Time: 3:26 PM
 */

namespace User\Controller;


class UserController
{
    private $repository;

    private $command;

    private $userManager;

    public function __construct($repository, $command, $userManager)
    {
        $this->repository = $repository;
        $this->command = $command;
        $this->userManager = $userManager;
    }

    public function indexAction(){}

    public function addAction(){}

    public function viewAction(){}

    public function editAction(){}

    public function changePasswordAction(){}

    public function resetPasswordAction(){}
}