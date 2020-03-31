<?php

namespace Cms\Model\Podcast\Sql\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Cms\Model\Podcast\Sql\PodcastDbSqlCommand;

class PodcastDbSqlCommandFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PodcastDbSqlCommand($container->get('Dtw\Db\Adapter'));
    }
}