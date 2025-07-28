<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\ResultExtractor;

use Ibexa\Contracts\Core\Persistence\Content\Handler;
use Ibexa\Contracts\Core\Persistence\Content\Location\Handler as LocationHandler;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use Ibexa\Solr\Gateway\EndpointRegistry;
use Ibexa\Solr\ResultExtractor;
use RuntimeException;
use stdClass;

/**
 * The Loading Result Extractor extracts the value object from the Solr search hit data
 * by loading it from the persistence.
 */
class LoadingResultExtractor extends ResultExtractor
{
    public function __construct(
        protected readonly Handler $contentHandler,
        protected readonly LocationHandler $locationHandler,
        protected AggregationResultExtractor $aggregationResultExtractor,
        protected EndpointRegistry $endpointRegistry
    ) {
        parent::__construct($aggregationResultExtractor, $endpointRegistry);
    }

    /**
     * @throws \RuntimeException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     */
    public function extractHit(stdClass $hit): ValueObject
    {
        if ($hit->document_type_id === 'content') {
            return $this->contentHandler->loadContentInfo($hit->content_id_id);
        }

        if ($hit->document_type_id === 'location') {
            return $this->locationHandler->load($hit->location_id_id);
        }

        throw new RuntimeException("Could not extract: document of type '{$hit->document_type_id}' is not handled.");
    }
}
