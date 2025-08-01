<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;

interface RangeAggregationKeyMapper
{
    /**
     * @param array{languages?: string[], languageCode?: string, useAlwaysAvailable?: bool} $languageFilter
     */
    public function map(Aggregation $aggregation, array $languageFilter, string $key): mixed;
}
