<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 2:09 PM
 */

namespace Player;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\Db\Adapter\AdapterAbstractServiceFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Player\Controller\Factory;
use Player\Model\Player\Sql;
use Laminas\Cache\Storage\Adapter\Filesystem;

return [
    'service_manager' => [
        'aliases' => [
            Model\Player\PlayerRepositoryInterface::class => Model\Player\Sql\SqlPlayerRepository::class,
            Model\Player\PlayerCommandInterface::class => Model\Player\Sql\SqlPlayerCommand::class,
            Model\Stats\StatsRepositoryInterface::class => Model\Stats\Sql\SqlStatsRepository::class,
            Model\Stats\StatsCommandInterface::class => Model\Stats\Sql\SqlStatsCommand::class,
            Model\Team\TeamRepositoryInterface::class => Model\Team\Sql\SqlTeamRepository::class,
            Model\Team\TeamCommandInterface::class => Model\Team\Sql\SqlTeamCommand::class,
            Model\Matchup\MatchupRepositoryInterface::class =>  Model\Matchup\Sql\SqlMatchupRepository::class,
            Model\Matchup\MatchupCommandInterface::class => Model\Matchup\Sql\SqlMatchupCommand::class
        ],
        'factories' => [
            'Dtw\Db\Adapter' => AdapterAbstractServiceFactory::class,
            Model\Player\Sql\SqlPlayerRepository::class => Model\Player\Sql\Factory\SqlPlayerRepositoryFactory::class,
            Model\Player\Sql\SqlPlayerCommand::class => Model\Player\Sql\Factory\SqlPlayerCommandFactory::class,
            Service\PlayerManager::class => Service\Factory\PlayerManagerFactory::class,
            Model\Stats\Sql\SqlStatsRepository::class => Model\Stats\Sql\Factory\SqlStatRepositoryFactory::class,
            Model\Stats\Sql\SqlStatsCommand::class => Model\Stats\Sql\Factory\SqlStatCommandFactory::class,
            Service\StatsManager::class => Service\Factory\StatsManagerFactory::class,
            Model\Team\Sql\SqlTeamRepository::class => Model\Team\Sql\Factory\SqlTeamRepositoryFactory::class,
            Model\Team\Sql\SqlTeamCommand::class => Model\Team\Sql\Factory\SqlTeamCommandFactory::class,
            Service\TeamManager::class => Service\Factory\TeamManagerFactory::class,
            Model\Matchup\Sql\SqlMatchupCommand::class => Model\Matchup\Sql\Factory\SqlMatchupCommandFactory::class,
            Model\Matchup\Sql\SqlMatchupRepository::class => Model\Matchup\Sql\Factory\SqlMatchupRepositoryFactory::class,
            Service\MatchupManager::class => Service\Factory\MatchupManagerFactory::class,
            Service\SportsInfoApi::class =>Service\Factory\SportsInfoApiFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\ScriptController::class => Controller\Factory\ScriptControllerFactory::class,
            Controller\PlayerController::class => Controller\Factory\PlayerControllerFactory::class,
            Controller\AdminController::class  => Controller\Factory\AdminControllerFactory::class,
            Controller\TeamController::class  => Controller\Factory\TeamControllerFactory::class,
            Controller\MatchupController::class => Controller\Factory\MatchupControllerFactory::class
        ],
    ],

    'caches' => [
        'teamCache' => [
            'adapter' => [
                'name'    => Filesystem::class,
                'options' => [
                    // Store cached data in this directory.
                    'cache_dir' => './data/cache',
                    // Store cached data for 1 hour.
                    'ttl' => 60*60*1
                ],
            ],
            'plugins' => [
                [
                    'name' => 'serializer',
                    'options' => [
                    ],
                ],
            ],
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
            'team' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/team[/:action[/:team]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\TeamController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'matchup' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/matchup[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\MatchupController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'admin' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/admin[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'sis_client_id' => 'tyrellcook@hotmail.com',
    'sis_client_secret' => 'iffGDt+V/1srEUVUp2tcknM2t0W3vOxUK7hCu3sN//A=',

    // The 'access_filter' key is used by the User module to restrict or permit
    // access to certain controller actions for unauthorized visitors.
    'access_filter' => [
        'options' => [
            // The access filter can work in 'restrictive' (recommended) or 'permissive'
            // mode. In restrictive mode all controller actions must be explicitly listed
            // under the 'access_filter' config key, and access is denied to any not listed
            // action for not logged in users. In permissive mode, if an action is not listed
            // under the 'access_filter' key, access to it is permitted to anyone (even for
            // not logged in users. Restrictive mode is more secure and recommended to use.
            'mode' => 'permissive'
        ],
        'controllers' => [
            Controller\AdminController::class => [
                // Allow authorized users to visit "settings" action
                ['actions' => '*', 'allow' => ['Admin']]
            ],
            Controller\ScriptController::class => [
                // Allow authorized users to visit "settings" action
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\MatchupController::class => [
                // Allow anyone to visit "index" and "about" actions
                //['actions' => ['index', 'about'], 'allow' => '*'],
                // Allow authorized users to visit "settings" action
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\PlayerController::class => [
                // Allow anyone to visit "index" and "about" actions
                ['actions' => ['search', 'view', 'rankings', 'compare', 'query'], 'allow' => '*'],
                // Allow authorized users to visit "settings" action
                ['actions' => ['settings'], 'allow' => '@']
            ],
            Controller\TeamController::class => [
                ['actions' => '*', 'allow' => '*']
            ]
        ]
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
                'qb-metrics' => [
                    'type'    => 'simple',  // This is the default, and may be omitted; more on
                    // types below
                    'options' => [
                        'route'    => 'update-qb-metrics',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'updateQbMetrics',
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
                        'route'    => 'update-sleeper-stats',
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
                'depth-charts' => [
                    'type'    => 'simple',  // This is the default, and may be omitted; more on
                    // types below
                    'options' => [
                        'route'    => 'build-depth-charts',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'buildDepthCharts',
                        ],
                    ],
                ],
                'update-teams' => [
                    'type'    => 'simple',  // This is the default, and may be omitted; more on
                    // types below
                    'options' => [
                        'route'    => 'update-teams',
                        'defaults' => [
                            'controller' => Controller\ScriptController::class,
                            'action'     => 'updateTeams',
                        ],
                    ],
                ],
            ],
        ],
    ],
];