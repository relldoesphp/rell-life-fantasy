<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/23/19
 * Time: 12:43 PM
 */

namespace User\Model\User\Sql\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use User\Model\User\Sql\SqlRepository;
use User\Model\User\User;
use Zend\Hydrator\Reflection as ReflectionHydrator;

class SqlRespositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SqlRepository(
            $container->get('Dtw\Db\Adapter'),
            new ReflectionHydrator(),
            new User()
        );
    }
}