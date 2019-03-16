<?php

namespace Blog;

use Zend\Router\Http\Literal;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Db\Adapter\AdapterAbstractServiceFactory;

return [
    'service_manager' => [
        'aliases' => [
            Model\PostRepositoryInterface::class => Model\ZendDbSqlRepository::class,
        ],
        'factories' => [
            'Rlf\Db\Adapter' => AdapterAbstractServiceFactory::class,
            Model\PostRepository::class => InvokableFactory::class,
            Model\ZendDbSqlRepository::class => Factory\ZendDbSqlRepositoryFactory::class,
        ],
    ],

    'router' => [
        'routes' => [
            'blog' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/blog',
                    'defaults' => [
                        'controller' => Controller\ListController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\ListController::class => Factory\ListControllerFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];