<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use stdClass;

final readonly class NestedAggregationResultExtractor implements AggregationResultExtractor
{
    public function __construct(
        private AggregationResultExtractor $innerResultExtractor,
        private string $nestedResultKey
    ) {
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $this->innerResultExtractor->canVisit($aggregation, $languageFilter);
    }

    public function extract(Aggregation $aggregation, array $languageFilter, stdClass $data): AggregationResult
    {
        return $this->innerResultExtractor->extract(
            $aggregation,
            $languageFilter,
            $data->{$this->nestedResultKey} ?? new stdClass()
        );
    }
}
