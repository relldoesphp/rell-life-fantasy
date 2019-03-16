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
//use Zend\Db\Adapter\AdapterInterface;


class SqlPlayerRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SqlPlayerRepository($container->get('Rlf\Db\Adapter'));
    }
}