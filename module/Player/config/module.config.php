<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 2:09 PM
 */

namespace Player;

use Zend\Router\Http\Segment;
use Zend\Db\Adapter\AdapterAbstractServiceFactory;
use Zend\ServiceManager\Factory\InvokableFactory;
use Player\Factory\SqlPlayerCommandFactory;

return [
    'service_manager' => [
        'aliases' => [
            // Update this line:
            Model\PlayerRepositoryInterface::class => Model\SqlPlayerRepository::class,
            // Add Command Center
            Model\PlayerCommandInterface::class => Model\SqlPlayerCommand::class,
        ],
        'factories' => [
            // Custom Mysql Adapter defined in configs
            'Rlf\Db\Adapter' => AdapterAbstractServiceFactory::class,
            // factory for sql repository
            Model\SqlPlayerRepository::class => Factory\SqlPlayerRepositoryFactory::class,
            // factory for Command Center
            Model\SqlPlayerCommand::class => Factory\SqlPlayerCommandFactory::class,
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
                    ],
                    'defaults' => [
                        'controller' => Controller\PlayerController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
                'wr-metrics' => [
                    'type'    => 'simple',  // This is the default, and may be omitted; more on
                    // types below
                    'options' => [
                        'route'    => 'update wr metrics',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'updatePercentiles',
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            // factory for script controller
            Controller\ScriptController::class => Factory\ScriptControllerFactory::class,
            // factory for player controller
            Controller\PlayerController::class => Factory\PlayerControllerFactory::class
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'player' => __DIR__ . '/../view',
        ],
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
        ],
    ],
];