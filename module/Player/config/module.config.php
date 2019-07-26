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
use Player\Controller\Factory;
use Player\Model\Player\Sql;

return [
    'service_manager' => [
        'aliases' => [
            Model\Player\PlayerRepositoryInterface::class => Model\Player\Sql\SqlPlayerRepository::class,
            Model\Player\PlayerCommandInterface::class => Model\Player\Sql\SqlPlayerCommand::class,
        ],
        'factories' => [
            'Dtw\Db\Adapter' => AdapterAbstractServiceFactory::class,
            Model\Player\Sql\SqlPlayerRepository::class => Model\Player\Sql\Factory\SqlPlayerRepositoryFactory::class,
            Model\Player\Sql\SqlPlayerCommand::class => Model\Player\Sql\Factory\SqlPlayerCommandFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\ScriptController::class => Controller\Factory\ScriptControllerFactory::class,
            Controller\PlayerController::class => Controller\Factory\PlayerControllerFactory::class,
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
];