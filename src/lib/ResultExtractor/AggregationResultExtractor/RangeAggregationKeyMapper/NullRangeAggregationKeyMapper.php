<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;

final class NullRangeAggregationKeyMapper implements RangeAggregationKeyMapper
{
    public function map(Aggregation $aggregation, array $languageFilter, string $key)
    {
        if ($key === '*') {
            return null;
        }

        return $key;
    }
}

class_alias(NullRangeAggregationKeyMapper::class, 'EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper\NullRangeAggregationKeyMapper');
