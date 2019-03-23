<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 3/22/19
 * Time: 8:55 AM
 */

namespace Player\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Player\Model\PlayerRepositoryInterface;
use Player\Controller\PlayerController;

class PlayerControllerFactory implements FactoryInterface
{
    /**
     * @return PlayerController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PlayerController($container->get(PlayerRepositoryInterface::class));
    }
}