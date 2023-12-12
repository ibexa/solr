<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\RangeAggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\RangeAggregationResultEntry;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;
use stdClass;

final class RangeAggregationResultExtractor implements AggregationResultExtractor
{
    /** @var string */
    private $aggregationClass;

    /** @var \Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper */
    private $keyMapper;

    public function __construct(string $aggregationClass, RangeAggregationKeyMapper $keyMapper)
    {
        $this->aggregationClass = $aggregationClass;
        $this->keyMapper = $keyMapper;
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof $this->aggregationClass;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation $aggregation
     */
    public function extract(Aggregation $aggregation, array $languageFilter, stdClass $data): AggregationResult
    {
        $entries = [];

        foreach ($data as $key => $bucket) {
            if ($key === 'count' || strpos($key, '_') === false) {
                continue;
            }

            $values = explode('_', $key, 2);
            $a = $this->keyMapper->map($aggregation, $languageFilter, $values[0]);
            $b = $this->keyMapper->map($aggregation, $languageFilter, $values[1]);

            foreach ($aggregation->getRanges() as $range) {
                if ($range->getFrom() == $a && $range->getTo() == $b) {
                    $entries[] = new RangeAggregationResultEntry($range, $bucket->count);
                    break;
                }
            }
        }

        $this->sort($aggregation, $entries);

        return new RangeAggregationResult($aggregation->getName(), $entries);
    }

    /**
     * Ensures that results entries are in the exact same order as they ware defined in aggregation.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation $aggregation
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\RangeAggregationResultEntry[] $entries
     */
    private function sort(AbstractRangeAggregation $aggregation, array &$entries): void
    {
        $order = $aggregation->getRanges();

        $comparator = static function (
            RangeAggregationResultEntry $a,
            RangeAggregationResultEntry $b
        ) use ($order): int {
            return array_search($a->getKey(), $order) <=> array_search($b->getKey(), $order);
        };

        usort($entries, $comparator);
    }
}

class_alias(RangeAggregationResultExtractor::class, 'EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationResultExtractor');
