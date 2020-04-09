<?php

namespace Cms\Form;

use Laminas\Form\Fieldset;
use Laminas\Form\Element;
use Cms\Model\Podcast\Podcast;
use Laminas\Hydrator\Reflection as ReflectionHydrator;

class PodcastFieldset extends Fieldset
{
    public function init()
    {
        $this->setHydrator(new ReflectionHydrator());
        $this->setObject(new Podcast());

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
            'name' => 'duration',
            'type' => 'text',
            'options' => [
                'label' => 'duration',
            ],
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'publishDate',
            'options' => [
                'label' => 'Publish Date',
//                'format' => 'Y-m-d',
            ],
            'attributes' => [
                'class' => 'datepicker',
            ],
        ]);


        $this->add([
            'type' => Element\Checkbox::class,
            'name' => 'active',
            'options' => [
                'label' => 'Publish',
                'use_hidden_element' => true,
                'checked_value' => '1',
                'unchecked_value' => '0',
            ],
        ]);

    }
}