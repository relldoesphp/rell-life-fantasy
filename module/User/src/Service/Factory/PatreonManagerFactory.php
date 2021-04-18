<?php


namespace User\Service\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Patreon\API;
use Patreon\OAuth;
use User\Service\PatreonManager;

class PatreonManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new PatreonManager();
    }
}