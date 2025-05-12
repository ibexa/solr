<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Solr\ApiLoader;

use Ibexa\Contracts\Core\Container\ApiLoader\RepositoryConfigurationProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IndexingDepthProviderFactory
{
    private ContainerInterface $container;

    private RepositoryConfigurationProviderInterface $repositoryConfigurationProvider;

    private string $defaultConnection;

    private string $indexingDepthProviderClass;

    public function __construct(
        ContainerInterface $container,
        RepositoryConfigurationProviderInterface $repositoryConfigurationProvider,
        string $defaultConnection,
        string $indexingDepthProviderClass
    ) {
        $this->container = $container;
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->defaultConnection = $defaultConnection;
        $this->indexingDepthProviderClass = $indexingDepthProviderClass;
    }

    public function buildService()
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        $connection = $this->defaultConnection;
        if (isset($repositoryConfig['search']['connection'])) {
            $connection = $repositoryConfig['search']['connection'];
        }

        return new $this->indexingDepthProviderClass(
            $this->container->getParameter(
                "ibexa.solr.connection.{$connection}.indexing_depth.map"
            ),
            $this->container->getParameter(
                "ibexa.solr.connection.{$connection}.indexing_depth.default"
            )
        );
    }
}
