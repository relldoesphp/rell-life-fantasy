<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/11/19
 * Time: 5:43 PM
 */

namespace Player\Model\Team\Sql\Factory;

use Player\Model\Team\Sql\SqlTeamRepository;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Player\Model\Team\Team;
use Zend\Hydrator\Reflection as ReflectionHydrator;


class SqlTeamRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SqlTeamRepository(
            $container->get('Dtw\Db\Adapter'),
            new ReflectionHydrator(),
            new Team()
        );
    }
}