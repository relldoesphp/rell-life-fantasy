<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/12/19
 * Time: 3:11 AM
 */

namespace Cms\Model\Article;


class Article
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $originalUrl;

    /**
     * @var string
     */
    private $summary;

    /**
     * @var string
     */
    private $headline;

    /**
     * @var string
     */
    private $author;

    /**
     * @var string
     */
    private $publishDate;

    /**
     * @var string
     */
    private $image;

    /**
     * @var integer
     */
    private $active;

    /**
     * @param string $title
     * @param string $text
     * @param int|null $id
     */
    public function __construct($title, $text, $id = null)
    {
        $this->title = $title;
        $this->text = $text;
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getOriginalUrl()
    {
        return $this->originalUrl;
    }

    /**
     * @param string $orginalUrl
     */
    public function setOriginalUrl($originalUrl)
    {
        $this->originalUrl = $originalUrl;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @return string
     */
    public function getHeadline()
    {
        return $this->headline;
    }

    /**
     * @param string $headline
     */
    public function setHeadline($headline)
    {
        $this->headline = $headline;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getPublishDate()
    {
        return $this->publishDate;
    }

    public function getDisplayDate()
    {
        $time = strtotime($this->publishDate);
        $datetime = date("M d, Y", $time);
        return $datetime;
    }

    /**
     * @param string $publishDate
     */
    public function setPublishDate($publishDate)
    {
        $this->publishDate = $publishDate;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    public function getDisplayImage()
    {
        if(empty($this->image)){
            return  "http://placehold.it/600x400";
        } else {
            return $this->image;
        }
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param int $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }


}