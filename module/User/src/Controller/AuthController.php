<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/22/19
 * Time: 3:35 PM
 */

namespace User\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\Authentication\Result;
use Laminas\Uri\Uri;
use User\Form\LoginForm;
use User\Model\User;
use User\Service\AuthManager;
use User\Model\User\RepositoryInterface;
use User\Service\PatreonManager;


class AuthController extends AbstractActionController
{

    private $repository;

    private $authManager;

    private $userManager;

    private $command;

    private $patreonManager;

    /**
     * Constructor.
     */
    public function __construct(RepositoryInterface $repository, $command, $authManager, $userManager, $patreonManager)
    {
        $this->repository = $repository;
        $this->command = $command;
        $this->authManager = $authManager;
        $this->userManager = $userManager;
        $this->patreonManager = $patreonManager;
    }

    /**
     * Authenticates user given email address and password credentials.
     */
    public function loginAction()
    {
        // Retrieve the redirect URL (if passed). We will redirect the user to this
        // URL after successfull login.
        $redirectUrl = (string)$this->params()->fromQuery('redirectUrl', '');
        if (strlen($redirectUrl)>2048) {
            throw new \Exception("Too long redirectUrl argument passed");
        }

        // Check if we do not have users in database at all. If so, create
        // the 'Admin' user.
        $this->userManager->createAdminUserIfNotExists();

        // Create login form
        $form = new LoginForm();
        $form->get('redirect_url')->setValue($redirectUrl);

        // Store login status.
        $isLoginError = false;

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Perform login attempt.
                $result = $this->authManager->login($data['email'],
                    $data['password'], $data['remember_me']);

                // Check result.
                if ($result->getCode()==Result::SUCCESS) {

                    // Get redirect URL.
                    $redirectUrl = $this->params()->fromPost('redirect_url', '');

                    if (!empty($redirectUrl)) {
                        // The below check is to prevent possible redirect attack
                        // (if someone tries to redirect user to another domain).
                        $uri = new Uri($redirectUrl);
                        if (!$uri->isValid() || $uri->getHost()!=null)
                            throw new \Exception('Incorrect redirect URL: ' . $redirectUrl);
                    }

                    // If redirect URL is provided, redirect the user to that URL;
                    // otherwise redirect to Home page.
                    if(empty($redirectUrl)) {
                        return $this->redirect()->toRoute('home');
                    } else {
                        $this->redirect()->toUrl($redirectUrl);
                    }
                } else {
                    $isLoginError = true;
                }
            } else {
                $isLoginError = true;
            }
        }

        $signUpLink = $this->patreonManager->getLoginButton();
        $code = (string)$this->params()->fromQuery('code', '');
        if (!empty($code)) {
            $tokens = $this->patreonManager->getTokens($code);
            $info = $this->patreonManager->getPatreonInfo($tokens['accessToken']);
            print "<pre>";
            print_r($info);
            print "</pre>";
            die();
        }

        return new ViewModel([
            'form' => $form,
            'isLoginError' => $isLoginError,
            'redirectUrl' => $redirectUrl,
            'signUpLink' => $signUpLink
        ]);
    }

    /**
     * The "logout" action performs logout operation.
     */
    public function logoutAction()
    {
        $this->authManager->logout();

        return $this->redirect()->toRoute('login');
    }

    public function patreonLoginAction()
    {




    }

}