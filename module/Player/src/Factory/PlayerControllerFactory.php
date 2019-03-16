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
use Player\Model\PlayerRepositoryInterface;

class PlayerControllerFactory implements FactoryInterface
{
    /**
     * @return PlayerController instance
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // TODO: Implement __invoke() method.
        return new PlayerController($container->get(PlayerRepositoryInterface::class));
    }
}