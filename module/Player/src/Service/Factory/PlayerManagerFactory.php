<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 8/4/19
 * Time: 7:34 PM
 */

namespace Player\Service\Factory;

use Player\Model\Stats\StatsCommandInterface;
use Player\Model\Stats\StatsRepositoryInterface;
use Laminas\ProgressBar\Adapter\Console;
use Player\Service\PlayerManager;
use Player\Model\Player\PlayerCommandInterface;
use Player\Model\Player\PlayerRepositoryInterface;
use Player\Model\Player;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class PlayerManagerFactory implements FactoryInterface
{
    /**
     * @return PlayerManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new PlayerManager(
            $container->get('Dtw\Db\Adapter'),
            new Console(),
            $container->get(PlayerCommandInterface::class),
            $container->get(PlayerRepositoryInterface::class),
            $container->get(StatsCommandInterface::class),
            $container->get(StatsRepositoryInterface::class)
        );
    }
}