<?php


namespace User\Service;

use User\Model\User;
use User\Model\Role;
use User\Model\Permission;
use User\Service\UserManager;
use Laminas\Permissions\Rbac\Rbac;
/**
 * This service is responsible for initialzing RBAC (Role-Based Access Control).
 */
class RbacManager
{
    /**
     * Doctrine entity manager.
     * @var UserManager
     */
    private $userManager;

    /**
     * RBAC service.
     * @var Rbac
     */
    private $rbac;

    /**
     * Auth service.
     * @var Zend\Authentication\AuthenticationService
     */
    private $authService;

    /**
     * Filesystem cache.
     * @var Zend\Cache\Storage\StorageInterface
     */
    private $cache;

    /**
     * Assertion managers.
     * @var array
     */
    private $assertionManagers = [];

    /**
     * Constructs the service.
     */
    public function __construct($userManager, $authService, $cache, $assertionManagers)
    {
        $this->userManager = $userManager;
        $this->authService = $authService;
        $this->cache = $cache;
        $this->assertionManagers = $assertionManagers;
    }

    /**
     * Initializes the RBAC container.
     */
    public function init($forceCreate = false)
    {
        // Create Rbac container.
        $rbac = new Rbac();

        // The following is to tell Rbac to create some parent roles if not exist yet
        $rbac->setCreateMissingRoles(true);

        // Create role hierarchy
        $rbac->addRole('Viewer', ['Editor', 'Author']);
        $rbac->addRole('Editor', ['Administrator']);
        $rbac->addRole('Author');
        $rbac->addRole('Administrator');

        // Assign permissions to the Viewer role.
        $rbac->getRole('Viewer')->addPermission('post.view');

        // Assign permissions to the Author role.
        $rbac->getRole('Author')->addPermission('post.own.edit');
        $rbac->getRole('Author')->addPermission('post.own.publish');

        // Assign permissions to the Editor role.
        $rbac->getRole('Editor')->addPermission('post.edit');
        $rbac->getRole('Editor')->addPermission('post.publish');
        // Assign permissions to the Administrator role.
        $rbac->getRole('Administrator')->addPermission('post.delete');
        // Save Rbac container to cache.
        $this->cache->setItem('rbac_container', $rbac);
        $this->rbac = $rbac;
    }

    public function hasLevel($user, $levels, $params = null)
    {
        if ($user==null) {
            $identity = $this->authService->getIdentity();
            if ($identity==null) {
                return false;
            }

            $user = $this->userManager->findUserByEmail($identity);
            if ($user==null) {
                // Oops.. the identity presents in session, but there is no such user in database.
                // We throw an exception, because this is a possible security problem.
                throw new \Exception('There is no user with such identity');
            }
        }

        if (in_array($user->getLevel(), $levels)) {
            return true;
        } else {
            return false;
        }
    }


    public function isGranted($user, $permission, $params = null)
    {
        if ($this->rbac==null) {
            $this->init();
        }

        if ($user==null) {
            $identity = $this->authService->getIdentity();
            if ($identity==null) {
                return false;
            }

            $user = $this->userManager->findUserByEmail($identity);
            if ($user==null) {
                // Oops.. the identity presents in session, but there is no such user in database.
                // We throw an exception, because this is a possible security problem.
                throw new \Exception('There is no user with such identity');
            }
        }

        $roles = $user->getRoles();

        foreach ($roles as $role) {
            if ($this->rbac->isGranted($role->getName(), $permission)) {
                if ($params==null)
                    return true;

                foreach ($this->assertionManagers as $assertionManager) {
                    if ($assertionManager->assert($this->rbac, $permission, $params))
                        return true;
                }
            }

            $parentRoles = $role->getParentRoles();
            foreach ($parentRoles as $parentRole) {
                if ($this->rbac->isGranted($parentRole->getName(), $permission)) {
                    return true;
                }
            }
        }
        return false;
    }
}