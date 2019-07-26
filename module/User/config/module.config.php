<?php

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\Db\Adapter\AdapterAbstractServiceFactory;
use Zend\ServiceManager\Factory\InvokableFactory;
use User\Service;
use User\Controller;

return [
    'service_manager' => [
        'aliases' => [
            User\Model\User\CommandInterface::class => User\Model\User\Sql\SqlCommand::class,
            User\Model\User\RepositoryInterface::class => User\Model\User\Sql\SqlRepository::class,
        ],
        'factories' => [
            /**** Factories for User Services ***/
            Zend\Authentication\AuthenticationService::class => User\Service\Factory\AuthenticationServiceFactory::class,
            User\Service\UserManager::class => User\Service\Factory\UserManagerFactory::class,
            User\Service\AuthManager::class => User\Service\Factory\AuthManagerFactory::class,
            User\Service\AuthAdapter::class => User\Service\Factory\AuthAdapterFactory::class,
            /**** Factories for User Model ***/
            User\Model\User\Sql\SqlRepository::class => User\Model\User\Sql\Factory\SqlRespositoryFactory::class,
            User\Model\User\Sql\SqlCommand::class => User\Model\User\Sql\Factory\SqlCommandFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            User\Controller\AuthController::class => User\Controller\Factory\AuthControllerFactory::class,
            User\Controller\UserController::class => User\Controller\Factory\UserControllerFactory::class,
        ]
    ],

    'router' => [
        'routes' => [
            'login' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/login',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/logout',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
            'reset-password' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/reset-password',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'resetPassword',
                    ],
                ],
            ],
            'set-password' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/set-password',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                        'action'     => 'setPassword',
                    ],
                ],
            ],
            'users' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/users[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\UserController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];