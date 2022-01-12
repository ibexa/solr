<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final class BooleanAggregationKeyMapper implements TermAggregationKeyMapper
{
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        return [
            true => true,
            false => false,
        ];
    }
}

class_alias(BooleanAggregationKeyMapper::class, 'EzSystems\EzPlatformSolrSearchEngine\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\BooleanAggregationKeyMapper');
