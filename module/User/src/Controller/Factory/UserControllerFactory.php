<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 7/23/19
 * Time: 9:30 PM
 */

namespace User\Controller\Factory;

use User\Controller\UserController;
use Interop\Container\ContainerInterface;
use User;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UserControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // TODO: Implement __invoke() method.
        return new UserController(
            $container->get(User\Service\UserManager::class)
        );
    }
}