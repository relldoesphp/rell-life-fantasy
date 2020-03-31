<?php
namespace Cms;

use Laminas\Router\Http\Literal;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Db\Adapter\AdapterAbstractServiceFactory;
use Laminas\Router\Http\Segment;

return [
    'service_manager' => [
        'aliases' => [
            Model\Article\ArticleRepositoryInterface::class => Model\Article\Sql\ZendDbSqlRepository::class,
            Model\Article\ArticleCommandInterface::class => Model\Article\Sql\ZendDbSqlCommand::class,
        ],
        'factories' => [
            'Dtw\Db\Adapter' => AdapterAbstractServiceFactory::class,
            Model\Article\Sql\ZendDbSqlRepository::class => Model\Article\Sql\Factory\ZendDbSqlRepositoryFactory::class,
            Model\Article\Sql\ZendDbSqlCommand::class => Model\Article\Sql\Factory\ZendDbSqlCommandFactory::class,
            Service\ArticleManager::class => Service\Factory\ArticleManagerFactory::class,
            Model\Podcast\Sql\PodcastDbSqlRepository::class => Model\Podcast\Sql\Factory\PodcastDbSqlRepositoryFactory::class,
            Model\Podcast\Sql\PodcastDbSqlCommand::class => Model\Podcast\Sql\Factory\PodcastDbSqlCommandFactory::class,
        ],
    ],

    'router' => [
        'routes' => [
            'article' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/articles',
                    'defaults' => [
                        'controller' => Controller\ArticleController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'detail' => [
                        'type' => Segment::class,
                        'options' => [
                            'route'    => '/:id',
                            'defaults' => [
                                'action' => 'view',
                            ],
//                            'constraints' => [
//                                'id' => '\d+',
//                            ],
                        ],
                    ],
                ],
            ],
            'cms' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/manage-articles',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'view' => [
                        'type' => Segment::class,
                        'options' => [
                            'route'    => '/:id',
                            'defaults' => [
                                'action' => 'view',
                            ],
                            'constraints' => [
                                'id' => '\d+',
                            ],
                        ],
                    ],
                    'add' => [
                        'type' => Literal::class,
                        'options' => [
                            'route'    => '/add',
                            'defaults' => [
                                'controller' => Controller\AdminController::class,
                                'action'     => 'addArticle',
                            ],
                        ],
                    ],
                    //edit
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route'    => '/edit/:id',
                            'defaults' => [
                                'controller' => Controller\AdminController::class,
                                'action'     => 'editArticle',
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'delete' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/delete/:id',
                            'defaults' => [
                                'controller' => Controller\AdminController::class,
                                'action'     => 'deleteArticle',
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
            ],
            'podcastAdmin' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/manage-podcasts',
                    'defaults' => [
                        'controller' => Controller\AdminController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'view' => [
                        'type' => Segment::class,
                        'options' => [
                            'route'    => '/:id',
                            'defaults' => [
                                'action' => 'view',
                            ],
                            'constraints' => [
                                'id' => '\d+',
                            ],
                        ],
                    ],
                    'add' => [
                        'type' => Literal::class,
                        'options' => [
                            'route'    => '/add',
                            'defaults' => [
                                'controller' => Controller\AdminController::class,
                                'action'     => 'addArticle',
                            ],
                        ],
                    ],
                    //edit
                    'edit' => [
                        'type' => Segment::class,
                        'options' => [
                            'route'    => '/edit/:id',
                            'defaults' => [
                                'controller' => Controller\AdminController::class,
                                'action'     => 'editArticle',
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                    'delete' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/delete/:id',
                            'defaults' => [
                                'controller' => Controller\AdminController::class,
                                'action'     => 'deleteArticle',
                            ],
                            'constraints' => [
                                'id' => '[1-9]\d*',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\ArticleController::class => Controller\Factory\ArticleControllerFactory::class,
            Controller\AdminController::class => Controller\Factory\AdminControllerFactory::class,
        ],
    ],

    'access_filter' => [
        'options' => [
            // The access filter can work in 'restrictive' (recommended) or 'permissive'
            // mode. In restrictive mode all controller actions must be explicitly listed
            // under the 'access_filter' config key, and access is denied to any not listed
            // action for not logged in users. In permissive mode, if an action is not listed
            // under the 'access_filter' key, access to it is permitted to anyone (even for
            // not logged in users. Restrictive mode is more secure and recommended to use.
            'mode' => 'restrictive'
        ],
        'controllers' => [
            Controller\AdminController::class => [
                // Allow authorized users to visit "settings" action
                ['actions' => '*', 'allow' => '@']
            ],
            Controller\ArticleController::class => [
                // Allow authorized users to visit "settings" action
                ['actions' => '*', 'allow' => '*']
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];