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

class SqlRespositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SqlRepository(
            $container->get('Dtw\Db\Adapter')
        );
    }
}