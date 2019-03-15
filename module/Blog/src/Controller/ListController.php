<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/12/19
 * Time: 2:51 AM
 */

namespace Blog\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Blog\Model\PostRepositoryInterface;
use Zend\View\Model\ViewModel;

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
}