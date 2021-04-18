<?php

namespace User;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\Db\Adapter\AdapterAbstractServiceFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use User\Service;
use User\Controller;
use User\View\Helper\Menu;
use Laminas\Cache\Storage\Adapter\Filesystem;


return [
    'service_manager' => [
        'aliases' => [
            Model\User\CommandInterface::class => Model\User\Sql\SqlCommand::class,
            Model\User\RepositoryInterface::class => Model\User\Sql\SqlRepository::class,
        ],
        'factories' => [
            /**** Factories for User Services ***/
            'Dtw\Db\Adapter' => AdapterAbstractServiceFactory::class,
            \Laminas\Authentication\AuthenticationService::class => Service\Factory\AuthenticationServiceFactory::class,
            Service\UserManager::class => Service\Factory\UserManagerFactory::class,
            Service\AuthManager::class => Service\Factory\AuthManagerFactory::class,
            Service\AuthAdapter::class => Service\Factory\AuthAdapterFactory::class,
            Service\RbacManager::class => Service\Factory\RbacManagerFactory::class,
            /**** Factories for User Model ***/
            Model\User\Sql\SqlRepository::class => Model\User\Sql\Factory\SqlRespositoryFactory::class,
            Model\User\Sql\SqlCommand::class => Model\User\Sql\Factory\SqlCommandFactory::class,
            Service\NavManager::class => Service\Factory\NavManagerFactory::class,
            Service\PatreonManager::class => Service\Factory\PatreonManagerFactory::class
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
            Controller\UserController::class => Controller\Factory\UserControllerFactory::class,
            Controller\AdminController::class => Controller\Factory\AdminControllerFactory::class,
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
            'patreon' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/patreon',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'patreonValidate',
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
                    'route'    => '/manage-users[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => Controller\AdminController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'not-authorized' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/not-authorized',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                        'action'     => 'notAuthorized',
                    ],
                ],
            ],
        ],
    ],

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
                ['actions' => '*', 'allow' => ['Member','Admin']]
            ],
            Controller\UserController::class => [
                // Allow authorized users to visit "settings" action
                ['actions' => '*', 'allow' => ['Member', 'Admin']]
            ],
            Controller\AuthController::class => [
                // Allow authorized users to visit "settings" action
                ['actions' => '*', 'allow' => '*']
            ],
        ],
    ],

    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
//        'not_found_template'       => 'error/404',
//        'exception_template'       => 'error/index',
//        'template_map' => [
//            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
//            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
//            'error/404'               => __DIR__ . '/../view/error/404.phtml',
//            'error/index'             => __DIR__ . '/../view/error/index.phtml',
//        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'view_helpers' => [
        'factories' => [
            View\Helper\Menu::class => View\Helper\Factory\MenuFactory::class,
            View\Helper\Breadcrumbs::class => InvokableFactory::class,
            View\Helper\CurrentUser::class => View\Helper\Factory\CurrentUserFactory::class,
        ],
        'aliases' => [
            'mainMenu' => View\Helper\Menu::class,
            'pageBreadcrumbs' => View\Helper\Breadcrumbs::class,
            'currentUser' => View\Helper\CurrentUser::class
        ],
    ],
    // The following key allows to define custom styling for FlashMessenger view helper.
    'view_helper_config' => [
        'flashmessenger' => [
            'message_open_format'      => '<div%s><ul><li>',
            'message_close_string'     => '</li></ul></div>',
            'message_separator_string' => '</li><li>'
        ]
    ],
];