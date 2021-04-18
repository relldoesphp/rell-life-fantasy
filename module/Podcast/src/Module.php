<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/24/19
 * Time: 8:38 PM
 */
namespace Podcast;

// Add these import statements:
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\Db\Adapter\AdapterAbstractServiceFactory;
use Podcast\Model;

class Module implements ConfigProviderInterface
{
    // getConfig() method is here
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    // Add this method:
    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\PodcastTable::class => function($container) {
                    $tableGateway = $container->get(Model\PodcastTableGateway::class);
                    return new Model\PodcastTable($tableGateway);
                },
                Model\PodcastTableGateway::class => function ($container) {
                    $dbAdapter = $container->get('Dtw\Db\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Podcast());
                    return new TableGateway('podcast', $dbAdapter, null, $resultSetPrototype);
                },
                'Dtw\Db\Adapter' => AdapterAbstractServiceFactory::class,
            ],
        ];
    }

    public function getControllerConfig()
    {
        return [
            'factories' => [
                Controller\PodcastController::class => function($container) {
                    return new Controller\PodcastController(
                        $container->get(Model\PodcastTable::class)
                    );
                },
            ],
        ];
    }
}