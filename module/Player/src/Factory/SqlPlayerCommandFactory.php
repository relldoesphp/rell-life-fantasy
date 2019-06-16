<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/16/19
 * Time: 5:31 PM
 */

namespace Player\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Player\Controller\PlayerController;
use Player\Model\SqlPlayerCommand;
use Zend\ProgressBar\Adapter\Console;
use Player\Model\SqlWrCommand;
use Player\Model\SqlRbCommand;
use Player\Model\SqlTeCommand;


class SqlPlayerCommandFactory implements FactoryInterface
{
    /**
     * @return SqlPlayerCommand instance
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SqlPlayerCommand(
            $container->get('Dtw\Db\Adapter'),
            new Console()
        );
    }
}