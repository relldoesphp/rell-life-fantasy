<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 4:54 PM
 */

namespace Player\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Player\Model\SqlPlayerRepository;
// we comment out the adapter interface because we are loading in custom adapter for DB object
//use Zend\Db\Adapter\AdapterInterface;
use Player\Model\Player;
use Zend\Hydrator\Reflection as ReflectionHydrator;


class SqlPlayerRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SqlPlayerRepository(
            $container->get('Rlf\Db\Adapter'),
            new ReflectionHydrator(),
            new Player()
        );
    }
}