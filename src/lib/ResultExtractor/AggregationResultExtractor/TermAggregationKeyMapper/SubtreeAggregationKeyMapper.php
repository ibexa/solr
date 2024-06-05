<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final class SubtreeAggregationKeyMapper implements TermAggregationKeyMapper
{
    /** @var \Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper */
    private $locationAggregationKeyMapper;

    public function __construct(TermAggregationKeyMapper $locationAggregationKeyMapper)
    {
        $this->locationAggregationKeyMapper = $locationAggregationKeyMapper;
    }

    /**
     * @param Aggregation\Location\SubtreeTermAggregation $aggregation
     */
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $ancestors = $this->getAncestors($aggregation->getPathString());
        $keys = array_filter($keys, static function ($key) use ($ancestors): bool {
            return !in_array($key, $ancestors);
        });

        return $this->locationAggregationKeyMapper->map($aggregation, $languageFilter, array_values($keys));
    }

    private function getAncestors(string $pathString): array
    {
        $ancestors = explode('/', trim($pathString, '/'));
        // Remove yourself from path
        array_pop($ancestors);

        return $ancestors;
    }
}
