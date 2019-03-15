<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/10/19
 * Time: 7:54 AM
 */

namespace Player\Controller;

use Player\Model\PlayerTable;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PlayerController extends AbstractActionController
{

    // Add this property:
    private $table;

    public function __construct(PlayerTable $table)
    {
        $this->table = $table;
    }


    public function indexAction()
    {
        return new ViewModel([
            'players' => $this->table->fetchAll(),
        ]);
    }

    public function viewAction()
    {

    }

    public function addAction()
    {
    }

    public function editAction()
    {
    }

    public function deleteAction()
    {
    }

}