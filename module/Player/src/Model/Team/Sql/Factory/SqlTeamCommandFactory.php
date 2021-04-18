<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 9/11/19
 * Time: 5:43 PM
 */

namespace Player\Model\Team\Sql\Factory;

use Player\Model\Team\Sql\SqlTeamCommand;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Laminas\ProgressBar\Adapter\Console;

class SqlTeamCommandFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SqlTeamCommand(
            $container->get('Dtw\Db\Adapter'),
            new Console()
        );
    }
}