<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Solr\ApiLoader;

use Ibexa\Contracts\Core\Container\ApiLoader\RepositoryConfigurationProviderInterface;
use Ibexa\Solr\FieldMapper\IndexingDepthProvider;
use Symfony\Component\DependencyInjection\ContainerInterface;

readonly class IndexingDepthProviderFactory
{
    /**
     * @param class-string<\Ibexa\Solr\FieldMapper\IndexingDepthProvider> $indexingDepthProviderClass
     */
    public function __construct(
        private ContainerInterface $container,
        private RepositoryConfigurationProviderInterface $repositoryConfigurationProvider,
        private string $defaultConnection,
        private string $indexingDepthProviderClass
    ) {
    }

    public function buildService(): IndexingDepthProvider
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        $connection = $this->defaultConnection;
        if (isset($repositoryConfig['search']['connection'])) {
            $connection = $repositoryConfig['search']['connection'];
        }

        return new $this->indexingDepthProviderClass(
            (array)$this->container->getParameter(
                "ibexa.solr.connection.{$connection}.indexing_depth.map"
            ),
            (int)$this->container->getParameter(
                "ibexa.solr.connection.{$connection}.indexing_depth.default"
            )
        );
    }
}
