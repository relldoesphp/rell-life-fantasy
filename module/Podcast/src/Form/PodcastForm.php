<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/25/19
 * Time: 9:46 PM
 */

namespace Podcast\Form;

use Laminas\Form\Form;

class PodcastForm extends Form
{
    public function __construct($name = null)
    {
        // We will ignore the name provided to the constructor
        parent::__construct('podcast');

        $this->add([
            'name' => 'id',
            'type' => 'hidden',
        ]);
        $this->add([
            'name' => 'title',
            'type' => 'text',
            'options' => [
                'label' => 'Title',
            ],
        ]);
        $this->add([
            'name' => 'description',
            'type' => 'textarea',
            'options' => [
                'label' => 'description',
            ],
        ]);
        $this->add([
            'name' => 'filepath',
            'type' => 'text',
            'options' => [
                'label' => 'File Path',
            ],
        ]);
        $this->add([
            'name' => 'shorttitle',
            'type' => 'text',
            'options' => [
                'label' => 'shorttitle',
            ],
        ]);
        $this->add([
            'name' => 'pubDate',
            'type' => 'text',
            'options' => [
                'label' => 'Publish Date',
            ],
        ]);
        $this->add([
            'name' => 'duration',
            'type' => 'text',
            'options' => [
                'label' => 'duration',
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Go',
                'id'    => 'submitbutton',
            ],
        ]);
    }
}