<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Solr\ApiLoader;

use Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider;
use Ibexa\Solr\FieldMapper\IndexingDepthProvider;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class IndexingDepthProviderFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var \Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider
     */
    private $repositoryConfigurationProvider;

    /**
     * @var string
     */
    private $defaultConnection;

    /**
     * @var string
     */
    private $indexingDepthProviderClass;

    public function __construct(
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        string $defaultConnection,
        string $indexingDepthProviderClass
    ) {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->defaultConnection = $defaultConnection;
        $this->indexingDepthProviderClass = $indexingDepthProviderClass;
    }

    public function buildService()
    {
        if ($this->container === null) {
            return new IndexingDepthProvider();
        }

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

class_alias(IndexingDepthProviderFactory::class, 'EzSystems\EzPlatformSolrSearchEngineBundle\ApiLoader\IndexingDepthProviderFactory');
