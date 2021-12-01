<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use stdClass;

final class DispatcherAggregationResultExtractor implements AggregationResultExtractor
{
    /** @var \Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor[] */
    private $extractors;

    /**
     * @param \Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor[] $extractors
     */
    public function __construct(iterable $extractors)
    {
        $this->extractors = $extractors;
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $this->findExtractor($aggregation, $languageFilter) !== null;
    }

    public function extract(Aggregation $aggregation, array $languageFilter, stdClass $data): AggregationResult
    {
        $extractor = $this->findExtractor($aggregation, $languageFilter);

        if ($extractor === null) {
            throw new NotImplementedException(
                'No result extractor available for aggregation: ' . get_class($aggregation)
            );
        }

        return $extractor->extract($aggregation, $languageFilter, $data);
    }

    private function findExtractor(
        Aggregation $aggregation,
        array $languageFilter
    ): ?AggregationResultExtractor {
        foreach ($this->extractors as $extractor) {
            if ($extractor->canVisit($aggregation, $languageFilter)) {
                return $extractor;
            }
        }

        return null;
    }
}

class_alias(DispatcherAggregationResultExtractor::class, 'EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\DispatcherAggregationResultExtractor');
