<?php
/**
 * Created by IntelliJ IDEA.
 * User: tcook
 * Date: 10/23/19
 * Time: 4:40 PM
 */

namespace Player\Service\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\ProgressBar\Adapter\Console;
use Player\Service\MatchupManager;
use Player\Model\Matchup\MatchupRepositoryInterface;
use Player\Model\Matchup\MatchupCommandInterface;
use Player\Model\Stats\StatsCommandInterface;
use Player\Model\Stats\StatsRepositoryInterface;

class MatchupManagerFactory implements FactoryInterface
{
    /**
     * @return MatchupManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new MatchupManager(
            $container->get('Dtw\Db\Adapter'),
            new Console(),
            $container->get(MatchupCommandInterface::class),
            $container->get(MatchupRepositoryInterface::class),
            $container->get(StatsCommandInterface::class),
            $container->get(StatsRepositoryInterface::class)
        );
    }
}