<?php

namespace Player;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\PlayerTable::class => function($container) {
                    $tableGateway = $container->get(Model\PlayerTableGateway::class);
                    return new Model\PlayerTable($tableGateway);
                },
                Model\PlayerTableGateway::class => function ($container) {
                    $dbAdapter = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Player());
                    return new TableGateway('player', $dbAdapter, null, $resultSetPrototype);
                },
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\PlayerController::class => function($container) {
                    return new Controller\PlayerController(
                        $container->get(Model\PlayerTable::class)
                    );
                },
            ],
        ];
    }
}