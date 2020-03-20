<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/12/19
 * Time: 2:51 AM
 */

namespace Blog\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Blog\Model\PostRepositoryInterface;
use Laminas\View\Model\ViewModel;
use InvalidArgumentException;

class ListController extends AbstractActionController
{

    private $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function indexAction()
    {
        return new ViewModel([
           'posts' => $this->postRepository->findAllPosts(),
        ]);
    }

    public function detailAction()
    {
        $id = $this->params()->fromRoute('id');

        try {
            $post = $this->postRepository->findPost($id);
        } catch (\InvalidArgumentException $ex) {
            return $this->redirect()->toRoute('blog');
        }

        return new ViewModel([
            'post' => $post,
        ]);
    }
}