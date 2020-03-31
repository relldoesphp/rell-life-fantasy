<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/12/19
 * Time: 2:51 AM
 */
namespace Cms\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Cms\Model\Article\ArticleRepositoryInterface;
use Laminas\View\Model\ViewModel;
use InvalidArgumentException;

class ArticleController extends AbstractActionController
{

    private $postRepository;

    public function __construct(ArticleRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function indexAction()
    {
        return new ViewModel([
           'posts' => $this->postRepository->findAllPosts(),
        ]);
    }

    public function viewAction()
    {
        $id = $this->params()->fromRoute('id');

        try {
            if (is_numeric($id)) {
                $post = $this->postRepository->findPost($id);
            } else {
                $post = $this->postRepository->findPostByUrl($id);
            }

            $recents = $this->postRepository->findRecentPosts(4);

        } catch (\InvalidArgumentException $ex) {
            return $this->redirect()->toRoute('article');
        }

        return new ViewModel([
            'post' => $post,
            'recents' => $recents
        ]);
    }
}