<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\StatsAggregationResult;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use stdClass;

final class StatsAggregationResultExtractor implements AggregationResultExtractor
{
    private string $aggregationClass;

    public function __construct(string $aggregationClass)
    {
        $this->aggregationClass = $aggregationClass;
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof $this->aggregationClass;
    }

    public function extract(Aggregation $aggregation, array $languageFilter, stdClass $data): AggregationResult
    {
        return new StatsAggregationResult(
            $aggregation->getName(),
            $data->count ?? null,
            $data->min ?? null,
            $data->max ?? null,
            $data->avg ?? null,
            $data->sum ?? null,
        );
    }
}
