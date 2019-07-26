<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/24/19
 * Time: 8:45 PM
 */
namespace Podcast;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Segment;

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


];