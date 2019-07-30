<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/29/19
 * Time: 11:32 AM
 */

namespace Player\Controller;

use Player\Model\Player;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Player\Model\Player\PlayerRepositoryInterface;
use Player\Form\PlayerForm;

class AdminController extends AbstractActionController
{
    private $playerRepository;

    private $playerList;

    public function __construct(Player\PlayerRepositoryInterface $playerRepository)
    {
        $this->playerRepository = $playerRepository;
        $this->playerList =  $this->playerRepository->getPlayerNames('Off');
    }

    public function indexAction()
    {
        $jsVars['list'] = $this->playerList;
        $viewModel = new ViewModel([
            'jsVars' => $jsVars,
        ]);

        return $viewModel;

    }

    public function editPlayerAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('admin', ['action' => 'index']);
        }

        // Retrieve the album with the specified id. Doing so raises
        // an exception if the album is not found, which should result
        // in redirecting to the landing page.
        try {
            $player = $this->playerRepository->findPlayer($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('admin', ['action' => 'index']);
        }

        $form = new PlayerForm();
        $form->bind($player);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];

        if (! $request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($player->getInputFilter());
        $form->setData($request->getPost());

        if (! $form->isValid()) {
            return $viewData;
        }

//        $this->($player);

        // Redirect to album list
        return $this->redirect()->toRoute('admin', ['action' => 'index']);
    }
}