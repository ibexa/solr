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
    /** @var \Ibexa\Bundle\Core\ApiLoader\RepositoryConfigurationProvider */
    private $repositoryConfigurationProvider;

    /** @var string */
    private $defaultConnection;

    /** @var string */
    private $searchEngineClass;

    /** @var \Ibexa\Solr\Gateway\GatewayRegistry */
    private $gatewayRegistry;

    /** @var \Ibexa\Solr\CoreFilter\CoreFilterRegistry */
    private $coreFilterRegistry;

    /** @var \Ibexa\Contracts\Core\Persistence\Content\Handler */
    private $contentHandler;

    /** @var \Ibexa\Contracts\Solr\DocumentMapper */
    private $documentMapper;

    /** @var \Ibexa\Solr\ResultExtractor */
    private $contentResultExtractor;

    /** @var \Ibexa\Solr\ResultExtractor */
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
