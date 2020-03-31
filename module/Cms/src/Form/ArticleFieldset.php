<?php

namespace Cms\Form;

use Laminas\Form\Fieldset;
use Laminas\Form\Element;
use Cms\Model\Article\Article;
use Laminas\Hydrator\Reflection as ReflectionHydrator;

class ArticleFieldset extends Fieldset
{
    public function init()
    {
        $this->setHydrator(new ReflectionHydrator());
        $this->setObject(new Article('', ''));

        $this->add([
            'type' => 'hidden',
            'name' => 'id',
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'title',
            'options' => [
                'label' => 'Article Title',
            ],
            "attributes" => [
                "placeholder" => "Title goes here",
                "class" => "form-control"
            ]
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'originalUrl',
            'options' => [
                'label' => 'Article URL',
            ],
            "attributes" => [
                "placeholder" => "URL goes here",
                "class" => "form-control"
            ]
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'headline',
            'options' => [
                'label' => 'Headline',
            ],
            "attributes" => [
                "placeholder" => "Headline goes here",
                "class" => "form-control"
            ]
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'summary',
            'options' => [
                'label' => 'Article Summary',
            ],
            "attributes" => [
                "placeholder" => "Summary goes here",
                "class" => "form-control"
            ]
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'author',
            'options' => [
                'label' => 'Article Author',
            ],
            "attributes" => [
                "placeholder" => "Me",
                "class" => "form-control"
            ]
        ]);

        $this->add([
            'type' => 'text',
            'name' => 'image',
            'options' => [
                'label' => 'Article Image',
            ],
            "attributes" => [
                "class" => "form-control"
            ]
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
//                'min' => '2012-01-01',
//                'max' => '2030-01-01',
//                'step' => '1', // days; default step interval is 1 day
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

        $this->add([
            'type' => 'textarea',
            'name' => 'text',
            'options' => [
                'label' => 'Article body',
            ],
        ]);
    }
}