<?php

namespace Blog;

use Laminas\Router\Http\Literal;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Db\Adapter\AdapterAbstractServiceFactory;
use Laminas\Router\Http\Segment;

return [
    'service_manager' => [
        'aliases' => [
            Model\PostRepositoryInterface::class => Model\ZendDbSqlRepository::class,
            Model\PostCommandInterface::class => Model\ZendDbSqlCommand::class,
        ],
        'factories' => [
            'Dtw\Db\Adapter' => AdapterAbstractServiceFactory::class,
            Model\ZendDbSqlRepository::class => Factory\ZendDbSqlRepositoryFactory::class,
            Model\ZendDbSqlCommand::class => Factory\ZendDbSqlCommandFactory::class,
            // 'Rlf\Db\Adapter' => AdapterAbstractServiceFactory::class,
            //Model\PostRepository::class => InvokableFactory::class,
            // Model\PostCommand::class => InvokableFactory::class,
        ],
    ],

    'router' => [
        'routes' => [
            'blog' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/blog',
                    'defaults' => [
                        'controller' => Controller\ListController::class,
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
                                'action' => 'detail',
                            ],
                            'constraints' => [
                                'id' => '\d+',
                            ],
                        ],
                    ],
                    // Add the following route:
                    'add' => [
                        'type' => Literal::class,
                        'options' => [
                            'route'    => '/add',
                            'defaults' => [
                                'controller' => Controller\WriteController::class,
                                'action'     => 'add',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\ListController::class => Factory\ListControllerFactory::class,
            // Add the following line:
            Controller\WriteController::class => Factory\WriteControllerFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];