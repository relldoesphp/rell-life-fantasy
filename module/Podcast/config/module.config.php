<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/24/19
 * Time: 8:45 PM
 */
namespace Podcast;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Segment;

return [

    'view_manager' => [
        'template_path_stack' => [
            'podcast' => __DIR__ . '/../view',
        ],
    ],

    // The following section is new and should be added to your file:
    'router' => [
        'routes' => [
            'podcast' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/podcasts[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\PodcastController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    // The 'access_filter' key is used by the User module to restrict or permit
    // access to certain controller actions for unauthorized visitors.
    'access_filter' => [
        'controllers' => [
            Controller\PodcastController::class => [
                // Allow authorized users to visit "settings" action
                ['actions' => ['index'], 'allow' => '*']
            ],
        ]
    ],




];