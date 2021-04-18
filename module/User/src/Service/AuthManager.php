<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/22/19
 * Time: 11:38 PM
 */

namespace User\Service;

use Laminas\Authentication\Result;
use Laminas\Session;
use \Exception;
use User\Model\User;

class AuthManager
{
    // Constants returned by the access filter.
    const ACCESS_GRANTED = 1; // Access to the page is granted.
    const AUTH_REQUIRED  = 2; // Authentication is required to see the page.
    const ACCESS_DENIED  = 3; // Access to the page is denied.
    /**
     * Authentication service.
     * @var \Laminas\Authentication\AuthenticationService
     */
    private $authService;

    private $sessionManager;

    private $patreonManager;

    private $rbacManager;

    /**
     * Contents of the 'access_filter' config key.
     * @var array
     */
    private $config;

    public function __construct($authService, $sessionManager, $config, $patreonManager, $userManager, $rbacManager) {
        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
        $this->config = $config;
        $this->patreonManager = $patreonManager;
        $this->userManager = $userManager;
        $this->rbacManager = $rbacManager;
    }
    /**
     * Performs a login attempt. If $rememberMe argument is true, it forces the session
     * to last for one month (otherwise the session expires on one hour).
     */
    public function login($email, $password, $rememberMe)
    {
        // Check if user has already logged in. If so, do not allow to log in
        // twice.
        if ($this->authService->getIdentity()!=null) {
            throw new \Exception('Already logged in');
        }

        // Authenticate with login/password.
        $authAdapter = $this->authService->getAdapter();
        $authAdapter->setEmail($email);
        $authAdapter->setPassword($password);
        $result = $this->authService->authenticate();

        // If user wants to "remember him", we will make session to expire in
        // one month. By default session expires in 1 hour (as specified in our
        // config/global.php file).
        if ($result->getCode()==Result::SUCCESS && $rememberMe) {
            // Session cookie will expire in 1 month (30 days).
            $this->sessionManager->rememberMe(60*60*24*30);
        }

        return $result;
    }

    public function patreonLogin($code)
    {
        //********** Patreon Authenticate ***********//
        if ($this->authService->getIdentity()!=null) {
            throw new \Exception('Already logged in');
        }

        $tokens = $this->patreonManager->getTokens($code);

        //******* If bad code return error ******//
        if (array_key_exists('error', $tokens)) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                ['Invalid code.']);
        }

        // Update or Create User with Valid Patreon Tokens
        $info = $this->patreonManager->getPatreonInfo($tokens['access_token']);
        $data = [
            "email" => $info['data']['attributes']['email'],
            "firstName" => $info['data']['attributes']['first_name'],
            "lastName" => $info['data']['attributes']['last_name'],
            "patreon_id" => $info['data']['id'],
            "patreon_image" => $info['data']['attributes']['image_url'],
            "patreon_token" => $tokens,
            "patreon_attributes" => $info['data']['attributes'],
            "patreon_membership" => $info['data']['relationships']['memberships']['data'],
            "status" => 1
        ];
        $user = $this->userManager->saveUser($data);
        //$user = $this->repository->getUserByEmail($this->email);
        // If there is no such user, return 'Identity Not Found' status.
        if ($user==null) {
            return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND,
                null,
                ['Invalid credentials.']);
        }
        // If the user with such email exists, we need to check if it is active or retired.
        // Do not allow retired users to log in.
        if ($user->getStatus()== User\User::STATUS_RETIRED) {
            return new Result(
                Result::FAILURE,
                null,
                ['User is retired.']);
        }

        // Successful login and update
        $result =  new Result(
            Result::SUCCESS,
            $user->getEmail(),
            ['Authenticated successfully.']);

        // Check if user has already logged in. If so, do not allow to log in
        // twice.
        if ($this->authService->getIdentity()!=null) {
            return $result;
        }

        if ($this->authService->hasIdentity()) {
            $this->authService->clearIdentity();
        }

        if ($result->isValid()) {
            $this->authService->getStorage()->write($result->getIdentity());
        }

        return $result;
    }

    /**
     * Performs user logout.
     */
    public function logout()
    {
        // Allow to log out only when user is logged in.
        if ($this->authService->getIdentity()==null) {
            throw new \Exception('The user is not logged in');
        }

        // Remove identity from session.
        $this->authService->clearIdentity();
    }

    /**
     * This is a simple access control filter. It allows vistors to visit certain pages only,
     * the rest requiring the user to be authenticated.
     *
     * This method uses the 'access_filter' key in the config file and determines
     * whenther the current visitor is allowed to access the given controller action
     * or not. It returns true if allowed; otherwise false.
     */
    public function filterAccess($controllerName, $actionName)
    {
        // Determine mode - 'restrictive' (default) or 'permissive'. In restrictive
        // mode all controller actions must be explicitly listed under the 'access_filter'
        // config key, and access is denied to any not listed action for unauthenticated users.
        // In permissive mode, if an action is not listed under the 'access_filter' key,
        // access to it is permitted to anyone (even for not logged in users.
        // Restrictive mode is more secure and recommended to use.
        $mode = isset($this->config['access_filter']['options']['mode'])?$this->config['access_filter']['options']['mode']:'restrictive';
        if ($mode!='restrictive' && $mode!='permissive')
            throw new \Exception('Invalid access filter mode (expected either restrictive or permissive mode');

        if (isset($this->config['access_filter']['controllers'][$controllerName])) {
            $items = $this->config['access_filter']['controllers'][$controllerName];
            foreach ($items as $item) {
                $actionList = $item['actions'];
                $allow = $item['allow'];
                if (is_array($actionList) && in_array($actionName, $actionList) ||
                    $actionList=='*') {
                    if ($allow=='*')
                        // Anyone is allowed to see the page.
                        return self::ACCESS_GRANTED;
                    else if (!$this->authService->hasIdentity()) {
                        if (is_array($allow)) {
                            // Only the user with this level is allowed to see the page
                            if ($this->rbacManager->hasLevel(null, $allow))
                                return self::ACCESS_GRANTED;
                            else
                                return self::ACCESS_DENIED;
                        } else {
                            // Only authenticated user is allowed to see the page.
                            return self::AUTH_REQUIRED;
                        }
                    }

                    if ($allow=='@') {
                        // Any authenticated user is allowed to see the page.
                        return self::ACCESS_GRANTED;
                    } else if (substr($allow, 0, 1)=='@') {
                        // Only the user with specific identity is allowed to see the page.
                        $identity = substr($allow, 1);
                        if ($this->authService->getIdentity()==$identity)
                            return self::ACCESS_GRANTED;
                        else
                            return self::ACCESS_DENIED;
                    } else if (is_array($allow)) {
                        // Only the user with this level is allowed to see the page
                        if ($this->rbacManager->hasLevel(null, $allow))
                            return self::ACCESS_GRANTED;
                        else
                            return self::ACCESS_DENIED;
                    } else {
                        throw new \Exception('Unexpected value for "allow" - expected ' .
                            'either "?", "@", "@identity" or "+permission"');
                    }
                }
            }
        }

        // In restrictive mode, we require authentication for any action not
        // listed under 'access_filter' key and deny access to authorized users
        // (for security reasons).
        if ($mode=='restrictive') {
            if(!$this->authService->hasIdentity())
                return self::AUTH_REQUIRED;
            else
                return self::ACCESS_DENIED;
        }

        // Permit access to this page.
        return true;
    }
}