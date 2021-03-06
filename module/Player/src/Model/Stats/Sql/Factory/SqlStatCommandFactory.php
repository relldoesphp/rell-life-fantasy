<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/11/19
 * Time: 2:03 AM
 */

namespace Player\Model\Stats\Sql\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Player\Model\Stats\Sql\SqlStatsCommand;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ProgressBar\Adapter\Console;

class SqlStatCommandFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SqlStatsCommand(
            $container->get('Dtw\Db\Adapter'),
            new Console()
        );
    }
}