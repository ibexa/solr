<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\ResultExtractor;

use Ibexa\Contracts\Core\Persistence\Content\ContentInfo;
use Ibexa\Contracts\Core\Persistence\Content\Location;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use Ibexa\Solr\Gateway\EndpointRegistry;
use Ibexa\Solr\Query\FacetFieldVisitor;

abstract class ResultExtractorDecorator extends NativeResultExtractor
{
    protected NativeResultExtractor $innerExtractor;

    public function __construct(
        NativeResultExtractor $innerExtractor,
        FacetFieldVisitor $facetBuilderVisitor,
        AggregationResultExtractor $aggregationResultExtractor,
        EndpointRegistry $endpointRegistry
    ) {
        parent::__construct($facetBuilderVisitor, $aggregationResultExtractor, $endpointRegistry);

        $this->innerExtractor = $innerExtractor;
    }

    /**
     * @param mixed $hit
     */
    public function extractHit($hit)
    {
        return $this->innerExtractor->extract($hit);
    }

    protected function extractContentInfoFromHit($hit): ContentInfo
    {
        return $this->innerExtractor->extractContentInfoFromHit($hit);
    }

    protected function extractLocationFromHit($hit): Location
    {
        return $this->innerExtractor->extractLocationFromHit($hit);
    }
}
