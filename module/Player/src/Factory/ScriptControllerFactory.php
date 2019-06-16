<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/22/19
 * Time: 9:36 AM
 */

namespace Player\Factory;

use Player\Model\PlayerRepositoryInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Player\Controller\ScriptController;
use Player\Model\PlayerCommandInterface;

class ScriptControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ScriptController
     */

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ScriptController(
            $container->get(PlayerCommandInterface::class),
            $container->get(PlayerRepositoryInterface::class)
        );
    }
}