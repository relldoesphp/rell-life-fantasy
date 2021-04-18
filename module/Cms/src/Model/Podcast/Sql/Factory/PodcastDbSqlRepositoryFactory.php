<?php


namespace Cms\Model\Podcast\Sql\Factory;

use Cms\Model\Podcast\Podcast;
use Interop\Container\ContainerInterface;
use Cms\Model\Podcast\Sql\PodcastDbSqlRepository;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Hydrator\Reflection as ReflectionHydrator;

class PodcastDbSqlRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PodcastDbSqlRepository(
            $container->get('Dtw\Db\Adapter'),
            new ReflectionHydrator(),
            new Podcast('', '')
        );
    }
}