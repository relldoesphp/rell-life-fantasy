<?php


namespace User\Service;

use User\Service\UserManager;
use Laminas\Permissions\Rbac\Rbac;
use Laminas\Authentication\AuthenticationService;
class RbacAssertionManager
{
    /**
     * Entity manager.
     * @var UserManager
     */
    private $userManager;

    /**
     * Auth service.
     * @var AuthenticationService
     */
    private $authService;

    /**
     * Constructs the service.
     */
    public function __construct($userManager, $authService)
    {
        $this->userManager = $userManager;
        $this->authService = $authService;
    }

    /**
     * This method is used for dynamic assertions.
     */
    public function assert(Rbac $rbac, $permission, $params)
    {
        $currentUser = $this->userManager->findUserByEmail($this->authService->getIdentity());

        if ($permission=='profile.own.view' && $params['user']->getId()==$currentUser->getId())
            return true;

        return false;
    }
}