<?php


namespace User\Controller;

use User\Form\UserForm;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use User\Service\UserManager;
use Laminas\Http\Response;
use User\Form\PasswordResetForm;
use User\Form\PasswordChangeForm;

class AdminController extends AbstractActionController
{

    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function indexAction()
    {
        $this->userManager->createAdminUserIfNotExists();
        $users = $this->userManager->getUsers();
        $viewModel = new ViewModel([
            'users' => $users,
        ]);
        return $viewModel;
    }

    public function addAction()
    {
        // Create user form
        $form = new UserForm('create', $this->userManager);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Add user.
                $user = $this->userManager->addUser($data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('users',
                    ['action'=>'view', 'id'=>$user->getId()]);
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    public function viewAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id < 1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Find a user with such ID.
        $user = $this->userManager->findUserById($id);

        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new ViewModel([
            'user' => $user
        ]);
    }

    public function editAction()
    {
        $id = (int)$this->params()->fromRoute('id', -1);
        if ($id < 1) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $user = $this->userManager->findUserById($id);

        if ($user == null) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        // Create user form
        $form = new UserForm('update', $this->userManager, $user);

        // Check if user has submitted the form
        if ($this->getRequest()->isPost()) {

            // Fill in the form with POST data
            $data = $this->params()->fromPost();

            $form->setData($data);

            // Validate form
            if ($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Update the user.
                $this->userManager->updateUser($user, $data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('users',
                    ['action' => 'view', 'id' => $user->getId()]);
            }
        } else {
            $form->setData(array(
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'status' => $user->getStatus(),
            ));
        }

        return new ViewModel(array(
            'user' => $user,
            'form' => $form
        ));
    }
}