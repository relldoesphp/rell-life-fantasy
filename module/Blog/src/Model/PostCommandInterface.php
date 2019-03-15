<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/14/19
 * Time: 10:45 PM
 */

namespace Blog\Model;


interface PostCommandInterface
{
    public function insertPost(Post $post);

    public function updatePost(Post $post);

    public function deletePost(Post $post);

}