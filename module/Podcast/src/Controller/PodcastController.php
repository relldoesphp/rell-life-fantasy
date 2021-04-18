<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/24/19
 * Time: 8:46 PM
 */

namespace Podcast\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Podcast\Model\PodcastTable;
use Podcast\Model\Podcast;
use Podcast\Form\PodcastForm;

class PodcastController extends AbstractActionController
{

    // Add this property:
    private $table;

    // Add this constructor:
    public function __construct(PodcastTable $table)
    {
        $this->table = $table;
    }

    public function indexAction()
    {
        return new ViewModel([
            'podcasts' => $this->table->fetchAll(),
        ]);
    }

    /* Update the following method to read as follows: */
    public function addAction()
    {
        $form = new PodcastForm();
        $form->get('submit')->setValue('Add');

        $request = $this->getRequest();

        if (! $request->isPost()) {
            return ['form' => $form];
        }

        $podcast = new Podcast();
        $form->setInputFilter($podcast->getInputFilter());
        $form->setData($request->getPost());

        if (! $form->isValid()) {
            return ['form' => $form];
        }

        $podcast->exchangeArray($form->getData());
        $this->table->savePodcast($podcast);
        return $this->redirect()->toRoute('podcasts');
    }



    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('podcast', ['action' => 'add']);
        }

        // Retrieve the album with the specified id. Doing so raises
        // an exception if the album is not found, which should result
        // in redirecting to the landing page.
        try {
            $podcast = $this->table->getPodcast($id);
        } catch (\Exception $e) {
            return $this->redirect()->toRoute('podcast', ['action' => 'index']);
        }

        $form = new PodcastForm();
        $form->bind($podcast);
        $form->get('submit')->setAttribute('value', 'Edit');

        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];

        if (! $request->isPost()) {
            return $viewData;
        }

        $form->setInputFilter($podcast->getInputFilter());
        $form->setData($request->getPost());

        if (! $form->isValid()) {
            return $viewData;
        }

        $this->table->savePodcast($podcast);

        // Redirect to album list
        return $this->redirect()->toRoute('podcast', ['action' => 'index']);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('podcast');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $id = (int) $request->getPost('id');
                $this->table->deletePodcast($id);
            }

            // Redirect to list of albums
            return $this->redirect()->toRoute('podcast');
        }

        return [
            'id'    => $id,
            'podcast' => $this->table->getPodcast($id),
        ];
    }
}