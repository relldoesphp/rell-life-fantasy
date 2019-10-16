<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/9/19
 * Time: 4:50 PM
 */

namespace User\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use User\View\Helper\Menu;
use User\Service\NavManager;

class MenuFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $navManager = $container->get(NavManager::class);

        // Get menu items.
        $items = $navManager->getMenuItems();

        // Instantiate the helper.
        return new Menu($items);
    }
}