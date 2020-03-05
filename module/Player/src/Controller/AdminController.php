<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/29/19
 * Time: 11:32 AM
 */

namespace Player\Controller;

use Player\Model\Player;
use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Player\Model\Player\PlayerRepositoryInterface;
use Player\Form\PlayerForm;

class AdminController extends AbstractActionController
{
    private $playerRepository;

    private $playerCommand;

    private $playerList;

    public function __construct(
        Player\PlayerRepositoryInterface $playerRepository,
        Player\PlayerCommandInterface $playerCommand)
    {
        $this->playerCommand = $playerCommand;
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

        if (!empty($player->getImages())) {
            $images = Json::decode($player->getImages(), 1);
            $i = 1;
            foreach ($images as $image) {
                $form->get("image{$i}")->setValue($image);
                $i++;
            }
        }

        $form->addFieldsets($player);

        $request = $this->getRequest();

        $viewData = ['id' => $id, 'form' => $form, 'player' => $player];

        if (! $request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($player->getInputFilter());

        $data = $request->getPost();
        $data['images'] = [
            $data['image1'],
            $data['image2'],
            $data['image3'],
            $data['image4'],
            $data['image5']
        ];

        foreach ($data['images'] as $key => $postImage) {
            if (empty($postImage)) {
                unset($data['images'][$key]);
            }
        }

        $player->setImages(json_encode($data['images']));
        $form->setData($data);

        if (! $form->isValid()) {
            return $viewData;
        }

        $player = $form->updatePlayer($player, $data);

        $this->playerCommand->save($player);

        // Redirect to album list
        return $this->redirect()->toRoute('admin', ['action' => 'index']);
    }
}