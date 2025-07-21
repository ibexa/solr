<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final readonly class LocationAggregationKeyMapper implements TermAggregationKeyMapper
{
    public function __construct(
        private LocationService $locationService
    ) {
    }

    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $result = [];

        $locations = $this->locationService->loadLocationList(array_map('intval', $keys));
        foreach ($locations as $id => $location) {
            $result["$id"] = $location;
        }

        return $result;
    }
}
