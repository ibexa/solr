<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Bundle\Solr\ApiLoader;

use Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider;
use Ibexa\Solr\FieldMapper\BoostFactorProvider;
use LogicException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * BoostFactorProvider service factory takes into account boost factor semantic configuration.
 */
class BoostFactorProviderFactory implements ContainerAwareInterface
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
    private $boostFactorProviderClass;

    /**
     * @param string $defaultConnection
     * @param string $boostFactorProviderClass
     */
    public function __construct(
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        $defaultConnection,
        $boostFactorProviderClass
    ) {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->defaultConnection = $defaultConnection;
        $this->boostFactorProviderClass = $boostFactorProviderClass;
    }

    public function buildService()
    {
        if ($this->container === null) {
            throw new LogicException(sprintf('Unable to build %s due to missing container reference', BoostFactorProvider::class));
        }

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

class_alias(BoostFactorProviderFactory::class, 'EzSystems\EzPlatformSolrSearchEngineBundle\ApiLoader\BoostFactorProviderFactory');
