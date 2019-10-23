<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 12:59 PM
 */

namespace Player\Model\Matchup\Sql\Factory;

use Player\Model\Matchup\Sql\SqlMatchupCommand;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\ProgressBar\Adapter\Console;

class SqlMatchupCommandFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SqlMatchupCommand(
            $container->get('Dtw\Db\Adapter'),
            new Console()
        );
    }
}
