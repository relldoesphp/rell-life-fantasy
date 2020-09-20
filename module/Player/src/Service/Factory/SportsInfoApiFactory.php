<?php


namespace Player\Service\Factory;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Player\Service\SportsInfoApi;

class SportsInfoApiFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        return new SportsInfoApi($config['sis_client_id'], $config['sis_client_secret']);
    }
}