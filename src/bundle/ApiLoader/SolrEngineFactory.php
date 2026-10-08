<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Solr\ApiLoader;

use Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider;
use Ibexa\Contracts\Core\Persistence\Content\Handler;
use Ibexa\Contracts\Solr\DocumentMapper;
use Ibexa\Solr\CoreFilter\CoreFilterRegistry;
use Ibexa\Solr\Gateway\GatewayRegistry;
use Ibexa\Solr\ResultExtractor;

class SolrEngineFactory
{
    /** @var RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    /** @var string */
    private $defaultConnection;

    /** @var string */
    private $searchEngineClass;

    /** @var GatewayRegistry */
    private $gatewayRegistry;

    /** @var CoreFilterRegistry */
    private $coreFilterRegistry;

    /** @var Handler */
    private $contentHandler;

    /** @var DocumentMapper */
    private $documentMapper;

    /** @var ResultExtractor */
    private $contentResultExtractor;

    /** @var ResultExtractor */
    private $locationResultExtractor;

    public function __construct(
        RepositoryConfigurationProvider $repositoryConfigurationProvider,
        $defaultConnection,
        $searchEngineClass,
        GatewayRegistry $gatewayRegistry,
        CoreFilterRegistry $coreFilterRegistry,
        Handler $contentHandler,
        DocumentMapper $documentMapper,
        ResultExtractor $contentResultExtractor,
        ResultExtractor $locationResultExtractor
    ) {
        $this->repositoryConfigurationProvider = $repositoryConfigurationProvider;
        $this->defaultConnection = $defaultConnection;
        $this->searchEngineClass = $searchEngineClass;
        $this->gatewayRegistry = $gatewayRegistry;
        $this->coreFilterRegistry = $coreFilterRegistry;
        $this->contentHandler = $contentHandler;
        $this->documentMapper = $documentMapper;
        $this->contentResultExtractor = $contentResultExtractor;
        $this->locationResultExtractor = $locationResultExtractor;
    }

    public function buildEngine()
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        $connection = $repositoryConfig['search']['connection'] ?? $this->defaultConnection;

        $gateway = $this->gatewayRegistry->getGateway($connection);
        $coreFilter = $this->coreFilterRegistry->getCoreFilter($connection);

        return new $this->searchEngineClass(
            $gateway,
            $this->contentHandler,
            $this->documentMapper,
            $this->contentResultExtractor,
            $this->locationResultExtractor,
            $coreFilter
        );
    }
}

class_alias(SolrEngineFactory::class, 'EzSystems\EzPlatformSolrSearchEngineBundle\ApiLoader\SolrEngineFactory');
