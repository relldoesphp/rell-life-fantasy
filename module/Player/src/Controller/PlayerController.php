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

    private $playerList;

    public function __construct(PlayerRepositoryInterface $playerRepository)
    {
        $this->playerRepository = $playerRepository;
        $this->playerList =  $this->playerRepository->getPlayerNames();
    }

    public function indexAction()
    {
         new ViewModel([
             'players' => $this->playerRepository->getPlayerNames(),
             'wr' => $this->playerRepository->getPlayerNames("WR"),
             'rb' => $this->playerRepository->getPlayerNames("RB"),
             'te' => $this->playerRepository->getPlayerNames("TE")
        ]);
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
                $position = $playerObject->getPosition();
                $playerObject->setMetrics(
                    $this->playerRepository->getPlayerMetrics($playerObject->getId(), $position)
                );
                $playerObject->setPercentiles(
                    $this->playerRepository->getPlayerPercentiles($playerObject->getId(), $position)
                );
                $playerObject->setScores(
                    $this->playerRepository->getTeamScores($playerObject->getTeam(), $position)
                );
                $players[$key] = $playerObject->getAllInfo();
            }
            $jsVars['players'] = $players;
        }

        $jsVars['list'] = $this->playerList;
        $jsVars['lists']['all'] = $this->playerList;
        $jsVars['lists']['WR'] = $this->playerRepository->getPlayerNames("WR");
        $jsVars['lists']['RB'] = $this->playerRepository->getPlayerNames("RB");
        $jsVars['list']['TE'] = $this->playerRepository->getPlayerNames("TE");
        $viewModel = new ViewModel([
            'players' => $players,
            'jsVars' => $jsVars,
        ]);

        return $viewModel;
    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        if (!is_integer($id)) {
            $player = $this->playerRepository->findPlayerByAlias($id);
        } else {
            $player = $this->playerRepository->findPlayer($id);
        }
        $position = $player->getPosition();
        $player->setMetrics(
            $this->playerRepository->getPlayerMetrics($player->getId(), $position)
        );
        $player->setPercentiles(
            $this->playerRepository->getPlayerPercentiles($player->getId(), $position)
        );
        $player->setScores(
            $this->playerRepository->getTeamScores($player->getTeam(), $position)
        );
        $playerData = $player->getAllInfo();

        $tableData = [];

        if (!empty($playerData['collegeStats'])) {
            $collegeStats = (array) $playerData['collegeStats'];
            foreach ($collegeStats as $year => $stats) {
                if ($position == "WR") {
                    $tableData[] = [
                        $year,
                        $stats->college,
                        $stats->class,
                        $stats->games,
                        $stats->receptions,
                        $stats->recYds,
                        $stats->recTds,
                        $stats->recAvg,
                        round($stats->recDominator,1)."%",
                        round($stats->ydsDominator,1)."%",
                        round($stats->tdDominator,1)."%",
                        ($stats->returnStats->kickYds + $stats->returnStats->puntYds),
                        ($stats->returnStats->kickTds + $stats->returnStats->puntTds),
                        ($stats->returnStats->returnDominator * 100)."%",
                    ];
                }

                if ($position == "TE") {
                    $tableData[] = [
                        $year,
                        $stats->college,
                        $stats->class,
                        $stats->games,
                        $stats->receptions,
                        $stats->recYds,
                        $stats->recTds,
                        $stats->recAvg,
                        round($stats->recDominator,1)."%",
                        round($stats->ydsDominator,1)."%",
                        round($stats->tdDominator,1)."%",
                    ];
                }

                if ($position == "RB") {
                    $tableData[] = [
                        $year,
                        $stats->college,
                        $stats->class,
                        $stats->games,
                        $stats->rushAtt,
                        $stats->rushYds,
                        $stats->rushAvg,
                        $stats->rushTds,
                        $stats->recs,
                        $stats->recYds,
                        $stats->recAvg,
                        $stats->recTds,
                        round(($stats->rushAtt / $stats->totals->carries) * 100, 1)."%",
                        round($stats->recDominator,1)."%",
                        round($stats->ydsDominator,1)."%",
                        round($stats->tdDominator,1)."%",
                    ];
                }


            }
        }

        $playerData['collegeTable'] = $tableData;
        $jsVars['player'] = $playerData;
        $jsVars['list'] = $this->playerList;
        $viewModel = new ViewModel([
            'player' => $playerData,
            'jsVars' => $jsVars,
        ]);

        switch ($playerData['position']){
            case 'WR':
                $viewModel->setTemplate('player/player/wr');
                break;
            case 'RB':
                $viewModel->setTemplate('player/player/wr');
                break;
            case 'TE':
                $viewModel->setTemplate('player/player/wr');
                break;
            case 'QB':
                $viewModel->setTemplate('player/player/qb');
                break;
            default:
        }
        return $viewModel;
    }

}