<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Bundle\Solr\ApiLoader;

use Ibexa\Contracts\Core\Container\ApiLoader\RepositoryConfigurationProviderInterface;
use Ibexa\Contracts\Core\Persistence\Content\Handler;
use Ibexa\Contracts\Solr\DocumentMapper;
use Ibexa\Solr\CoreFilter\CoreFilterRegistry;
use Ibexa\Solr\Gateway\GatewayRegistry;
use Ibexa\Solr\ResultExtractor;

readonly class SolrEngineFactory
{
    public function __construct(
        private RepositoryConfigurationProviderInterface $repositoryConfigurationProvider,
        private string $defaultConnection,
        private string $searchEngineClass,
        private GatewayRegistry $gatewayRegistry,
        private CoreFilterRegistry $coreFilterRegistry,
        private Handler $contentHandler,
        private DocumentMapper $documentMapper,
        private ResultExtractor $contentResultExtractor,
        private ResultExtractor $locationResultExtractor
    ) {
    }

    public function buildEngine(): \Ibexa\Solr\Handler
    {
        $repositoryConfig = $this->repositoryConfigurationProvider->getRepositoryConfig();

        $connection = $repositoryConfig['search']['connection'] ?? $this->defaultConnection;

        $gateway = $this->gatewayRegistry->getGateway($connection);
        $coreFilter = $this->coreFilterRegistry->getCoreFilter($connection);

        /** @var \Ibexa\Solr\Handler */
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
