<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;

use eZ\Publish\API\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;

final class FloatRangeAggregationKeyMapper implements RangeAggregationKeyMapper
{
    public function map(Aggregation $aggregation, array $languageFilter, string $key)
    {
        if ($key === '*') {
            return null;
        }

        return (float)$key;
    }
}

class_alias(FloatRangeAggregationKeyMapper::class, 'EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper\FloatRangeAggregationKeyMapper');
