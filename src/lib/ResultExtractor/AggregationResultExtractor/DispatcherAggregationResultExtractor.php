<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use stdClass;

final readonly class DispatcherAggregationResultExtractor implements AggregationResultExtractor
{
    /**
     * @param \Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor[] $extractors
     */
    public function __construct(
        private iterable $extractors
    ) {
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
                'No result extractor available for aggregation: ' . $aggregation::class
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
