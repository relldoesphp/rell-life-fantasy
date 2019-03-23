<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 3:13 PM
 */

namespace Player\Controller;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use Player\Model\PlayerRepositoryInterface;

class PlayerController extends AbstractActionController
{
    private $playerRepository;

    public function __construct(PlayerRepositoryInterface $playerRepository)
    {
        $this->playerRepository = $playerRepository;
    }

    public function indexAction()
    {
         new ViewModel([
            'players' => $this->playerRepository->getPlayerNames(),
        ]);

    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        if (!is_integer($id)) {
            $player = $this->playerRepository->findPlayerByAlias($id);
        } else {
            $player = $this->playerRepository->findPlayer($id);
        }
        $player->setMetrics($this->playerRepository->getPlayerMetrics($player->getId()));
        $player->setPercentiles($this->playerRepository->getPlayerPercentiles($player->getId()));
        $playerData = $player->getAllInfo();
        $jsVars['player'] = $playerData;
        $viewModel = new ViewModel([
            'player' => $playerData,
            'jsVars' => $jsVars,
        ]);

        $playerData['position'] = 'WR';

        switch ($playerData['position']){
            case 'WR':
                $viewModel->setTemplate('player/player/wr');
                break;
            case 'RB':
                $viewModel->setTemplate('player/player/rb');
                break;
            case 'TE':
                $viewModel->setTemplate('player/player/te');
                break;
            case 'QB':
                $viewModel->setTemplate('player/player/qb');
                break;
            default:
        }

        return $viewModel;
    }

}