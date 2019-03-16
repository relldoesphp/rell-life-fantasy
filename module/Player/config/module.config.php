<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 2:09 PM
 */

namespace Player;

use Player\Factory\PlayerControllerFactory;
use Zend\Router\Http\Segment;
use Zend\Db\Adapter\AdapterAbstractServiceFactory;

return [
    'service_manager' => [
        'aliases' => [
            // Update this line:
            Model\PlayerRepositoryInterface::class => Model\SqlPlayerRepository::class,
        ],
        'factories' => [
            // Add this line:
            Model\SqlPlayerRepository::class => Factory\SqlPlayerRepositoryFactory::class,
            'Rlf\Db\Adapter' => AdapterAbstractServiceFactory::class,
        ],
    ],

    'router' => [
        'routes' => [
            'player' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/player[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\PlayerController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\PlayerController::class => PlayerControllerFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'player' => __DIR__ . '/../view',
        ],
    ],
];