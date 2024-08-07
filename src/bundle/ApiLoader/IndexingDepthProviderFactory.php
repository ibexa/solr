<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\Solr\ApiLoader;

use Ibexa\Contracts\Core\Container\ApiLoader\RepositoryConfigurationProviderInterface;
use Ibexa\Solr\FieldMapper\IndexingDepthProvider;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class IndexingDepthProviderFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private RepositoryConfigurationProviderInterface $repositoryConfigurationProvider;

    /**
     * @var string
     */
    private $defaultConnection;

    /**
     * @var string
     */
    private $indexingDepthProviderClass;

    public function __construct(
        RepositoryConfigurationProviderInterface $repositoryConfigurationProvider,
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
            throw new LogicException(sprintf('Unable to build %s due to missing container reference', IndexingDepthProvider::class));
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
