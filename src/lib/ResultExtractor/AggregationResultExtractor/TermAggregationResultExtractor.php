<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\NullAggregationKeyMapper;
use stdClass;

final class TermAggregationResultExtractor implements AggregationResultExtractor
{
    private TermAggregationKeyMapper $keyMapper;

    private string $aggregationClass;

    public function __construct(string $aggregationClass, TermAggregationKeyMapper $keyMapper = null)
    {
        if ($keyMapper === null) {
            $keyMapper = new NullAggregationKeyMapper();
        }

        $this->keyMapper = $keyMapper;
        $this->aggregationClass = $aggregationClass;
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof $this->aggregationClass;
    }

    public function extract(Aggregation $aggregation, array $languageFilter, stdClass $data): AggregationResult
    {
        $entries = [];

        if (isset($data->buckets)) {
            $mappedKeys = $this->keyMapper->map(
                $aggregation,
                $languageFilter,
                $this->getKeys($data)
            );

            foreach ($data->buckets as $bucket) {
                $key = $bucket->val;

                if (isset($mappedKeys[$key])) {
                    $entries[] = new TermAggregationResultEntry(
                        $mappedKeys[$key],
                        $bucket->count
                    );
                }
            }
        }

        return new TermAggregationResult($aggregation->getName(), $entries);
    }

    private function getKeys(stdClass $data): array
    {
        $keys = [];
        foreach ($data->buckets as $bucket) {
            $keys[] = $bucket->val;
        }

        return $keys;
    }
}
