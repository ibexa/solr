<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\ResultExtractor;

use Ibexa\Contracts\Core\Persistence\Content\Handler as ContentHandler;
use Ibexa\Contracts\Core\Persistence\Content\Location\Handler as LocationHandler;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use Ibexa\Solr\Gateway\EndpointRegistry;
use Ibexa\Solr\Query\FacetFieldVisitor;
use Ibexa\Solr\ResultExtractor;
use RuntimeException;

/**
 * The Loading Result Extractor extracts the value object from the Solr search hit data
 * by loading it from the persistence.
 */
class LoadingResultExtractor extends ResultExtractor
{
    /**
     * Content handler.
     *
     * @var \Ibexa\Contracts\Core\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * Location handler.
     *
     * @var \Ibexa\Contracts\Core\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    public function __construct(
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        FacetFieldVisitor $facetFieldVisitor,
        AggregationResultExtractor $aggregationResultExtractor,
        EndpointRegistry $endpointRegistry
    ) {
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;

        parent::__construct($facetFieldVisitor, $aggregationResultExtractor, $endpointRegistry);
    }

    /**
     * Extracts value object from $hit returned by Solr backend.
     *
     * @throws \RuntimeException If search $hit could not be handled
     *
     * @param mixed $hit
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ValueObject
     */
    public function extractHit($hit)
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

class_alias(LoadingResultExtractor::class, 'EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\LoadingResultExtractor');
