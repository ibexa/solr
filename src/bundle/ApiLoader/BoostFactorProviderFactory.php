<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Solr\ApiLoader;

use Ibexa\Contracts\Core\Container\ApiLoader\RepositoryConfigurationProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * BoostFactorProvider service factory takes into account boost factor semantic configuration.
 */
class BoostFactorProviderFactory
{
    private ContainerInterface $container;

    private RepositoryConfigurationProviderInterface $repositoryConfigurationProvider;

    /**
     * @var string
     */
    private $defaultConnection;

    /**
     * @var string
     */
    private $boostFactorProviderClass;

    /**
     * @param string $defaultConnection
     * @param string $boostFactorProviderClass
     */
    public function __construct(
        ContainerInterface $container,
        RepositoryConfigurationProviderInterface $repositoryConfigurationProvider,
        $defaultConnection,
        $boostFactorProviderClass
    ) {
        $this->container = $container;
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->defaultConnection = $defaultConnection;
        $this->boostFactorProviderClass = $boostFactorProviderClass;
    }

    public function buildService()
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        $connection = $this->defaultConnection;
        if (isset($repositoryConfig['search']['connection'])) {
            $connection = $repositoryConfig['search']['connection'];
        }

        return new $this->boostFactorProviderClass(
            $this->container->getParameter(
                "ibexa.solr.connection.{$connection}.boost_factor_map_id"
            )
        );
    }
}
