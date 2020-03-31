<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/14/19
 * Time: 10:45 PM
 */

namespace Cms\Model\Article;


interface ArticleCommandInterface
{
    public function insertPost(Article $post);

    public function updatePost(Article $post);

    public function deletePost(Article $post);

}