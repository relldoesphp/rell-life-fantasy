<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 1:00 AM
 */

namespace Cms\Model\Article\Sql\Factory;

use Interop\Container\ContainerInterface;
use Cms\Model\Article\Sql\ZendDbSqlRepository;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Cms\Model\Article\Article;
use Laminas\Hydrator\Reflection as ReflectionHydrator;

class ZendDbSqlRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ZendDbSqlRepository(
            $container->get('Dtw\Db\Adapter'),
            new ReflectionHydrator(),
            new Article('', '')
        );
    }

}