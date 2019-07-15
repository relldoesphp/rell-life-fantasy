<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 2:09 PM
 */

namespace Player;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\Db\Adapter\AdapterAbstractServiceFactory;
use Zend\ServiceManager\Factory\InvokableFactory;
use Player\Factory\SqlPlayerCommandFactory;
use Player\Factory\SqlPlayerRepositoryFactory;

return [
    'service_manager' => [
        'aliases' => [
            // Update this line:
            Model\Player\PlayerRepositoryInterface::class => Model\Player\SqlPlayerRepository::class,
            // Add Command Center
            Model\Player\PlayerCommandInterface::class => Model\Player\SqlCommands\SqlPlayerCommand::class,
        ],
        'factories' => [
            // Custom Mysql Adapter defined in configs
            'Dtw\Db\Adapter' => AdapterAbstractServiceFactory::class,
            // factory for sql repository
            Model\Player\SqlPlayerRepository::class => Factory\SqlPlayerRepositoryFactory::class,
            // factory for Command Center
            Model\Player\SqlCommands\SqlPlayerCommand::class => Factory\SqlPlayerCommandFactory::class,
        ],
    ],

    'router' => [
        'routes' => [
            'playerHome' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/player/',
                    'defaults' => [
                        'controller' => Controller\PlayerController::class,
                        'action'     => 'search',
                    ],
                ],
            ],
            'player' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/player[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\PlayerController::class,
                        'action'     => 'search',
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
                        'route'    => 'update-wr-metrics',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'updateWrMetrics',
                        ],
                    ],
                ],
                'rb-metrics' => [
                    'type'    => 'simple',  // This is the default, and may be omitted; more on
                    // types below
                    'options' => [
                        'route'    => 'update-rb-metrics',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'updateRbMetrics',
                        ],
                    ],
                ],
                'te-metrics' => [
                    'type'    => 'simple',  // This is the default, and may be omitted; more on
                    // types below
                    'options' => [
                        'route'    => 'update-te-metrics',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'updateTeMetrics',
                        ],
                    ],
                ],
                'data-scrap' => [
                    'type'    => 'simple',  // This is the default, and may be omitted; more on
                    // types below
                    'options' => [
                        'route'    => 'data-scrapper',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'dataScrapper',
                        ],
                    ],
                ],
                'sleeper-stats' => [
                    'type'    => 'simple',  // This is the default, and may be omitted; more on
                    // types below
                    'options' => [
                        'route'    => 'get-sleeper-stats',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'getSleeperStats',
                        ],
                    ],
                ],
                'make-json' => [
                    'type'    => 'simple',  // This is the default, and may be omitted; more on
                    // types below
                    'options' => [
                        'route'    => 'make-name-json',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'makeNameJson',
                        ],
                    ],
                ],
                'update-sleeper-info' => [
                    'type' => 'simple',  // This is the default, and may be omitted; more on
                    // types below
                    'options' => [
                        'route'    => 'update-sleeper-info',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'updateSleeperInfo',
                        ],
                    ],
                ],
                'sleeper-logs' => [
                    'type' => 'simple',  // This is the default, and may be omitted; more on
                    // types below
                    'options' => [
                        'route'    => 'update-sleeper-logs',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'getSleeperLogs',
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
            'layout/layout'           => __DIR__ . '/../view/layout/startupLayout.phtml',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];