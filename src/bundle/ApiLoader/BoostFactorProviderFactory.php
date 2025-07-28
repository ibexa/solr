<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Solr\ApiLoader;

use Ibexa\Contracts\Core\Container\ApiLoader\RepositoryConfigurationProviderInterface;
use Ibexa\Solr\FieldMapper\BoostFactorProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * BoostFactorProvider service factory takes into account boost factor semantic configuration.
 */
readonly class BoostFactorProviderFactory
{
    /**
     * @param class-string<\Ibexa\Solr\FieldMapper\BoostFactorProvider> $boostFactorProviderClass
     */
    public function __construct(
        private ContainerInterface $container,
        private RepositoryConfigurationProviderInterface $repositoryConfigurationProvider,
        private string $defaultConnection,
        private string $boostFactorProviderClass
    ) {
    }

    public function buildService(): BoostFactorProvider
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        $connection = $this->defaultConnection;
        if (isset($repositoryConfig['search']['connection'])) {
            $connection = $repositoryConfig['search']['connection'];
        }

        return new $this->boostFactorProviderClass(
            (array)$this->container->getParameter(
                "ibexa.solr.connection.{$connection}.boost_factor_map_id"
            )
        );
    }
}
