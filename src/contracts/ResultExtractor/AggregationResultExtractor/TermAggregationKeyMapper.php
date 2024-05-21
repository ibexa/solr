<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;

interface TermAggregationKeyMapper
{
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array;
}
