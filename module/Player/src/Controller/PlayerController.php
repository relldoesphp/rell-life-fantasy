<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 3:13 PM
 */

namespace Player\Controller;

use Player\Model\Player;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Player\Model\Player\PlayerRepositoryInterface;

class PlayerController extends AbstractActionController
{
    private $playerRepository;

    private $playerList;

    public function __construct(Player\PlayerRepositoryInterface $playerRepository)
    {
        $this->playerRepository = $playerRepository;
        $this->playerList =  $this->playerRepository->getPlayerNames('Off');
    }

    public function searchAction()
    {
        $jsVars['list'] = $this->playerList;
        $jsVars['lists']['all'] = $this->playerList;

        $jsVars['lists']['WR'] = $this->playerRepository->getPlayerNames("WR");
        $jsVars['lists']['RB'] = $this->playerRepository->getPlayerNames("RB");
        $jsVars['lists']['TE'] = $this->playerRepository->getPlayerNames("TE");
        $viewModel = new ViewModel([
            'jsVars' => $jsVars,
        ]);

        return $viewModel;
    }

    public function compareAction()
    {
        $id1 = $this->params()->fromQuery("player1");
        $id2 = $this->params()->fromQuery("player2");
        $players = [];
        if ($id1) {
            $players[] = [
                'id' => $id1
            ];
        }

        if ($id2) {
            $players[] = [
                'id' => $id2
            ];
        }

        if (!empty($players)) {
            foreach ($players as $key => $player) {
                $playerObject = $this->playerRepository->findPlayer($player['id']);
                if ($playerObject == false ) {
                    $playerObject = new Player();
                }
                $players[$key] = $playerObject->getAllInfo();
            }
        } else {
            $player1 = new Player();
            $player2 = new Player();
            $players[0] = $player1->getAllInfo();
            $players[1] = $player2->getAllInfo();
        }

        $jsVars['players'] = $players;
        $jsVars['list'] = $this->playerList;
        $sjVars['lists']['all'] = $this->playerList;
        $jsVars['lists']['WR'] = $this->playerRepository->getPlayerNames("WR");
        $jsVars['lists']['RB'] = $this->playerRepository->getPlayerNames("RB");
        $jsVars['lists']['TE'] = $this->playerRepository->getPlayerNames("TE");
        $viewModel = new ViewModel([
            'players' => $players,
            'jsVars' => $jsVars,
        ]);

        return $viewModel;
    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        if (is_numeric($id)) {
            $player = $this->playerRepository->findPlayer($id);
        } else {
            $player = $this->playerRepository->findPlayerByAlias($id);
        }

        $playerData = $player->getAllInfo();

        $jsVars['player'] = $playerData;
        $jsVars['list'] = $this->playerList;

        $viewModel = new ViewModel([
            'player' => $playerData,
            'jsVars' => $jsVars,
        ]);

//        switch ($playerData['position']) {
//            case 'WR':
//                $viewModel->setTemplate('player/player/view');
//                break;
//            case 'RB':
//                $viewModel->setTemplate('player/player/');
//                break;
//            case 'TE':
//                $viewModel->setTemplate('player/player/wr');
//                break;
//            case 'QB':
//                $viewModel->setTemplate('player/player/qb');
//                break;
//            default:
//                $viewModel->setTemplate('player/player/wr');
//        }
        return $viewModel;
    }

    public function queryAction()
    {
        $query = $this->params()->fromRoute('id', '');
        $results = $this->playerRepository->queryPlayers($query);
        return new JsonModel($results);
    }
}