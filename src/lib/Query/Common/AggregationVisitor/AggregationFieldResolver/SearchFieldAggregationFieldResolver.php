<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver;

final readonly class SearchFieldAggregationFieldResolver implements AggregationFieldResolver
{
    public function __construct(
        private string $searchIndexFieldName
    ) {
    }

    public function resolveTargetField(Aggregation $aggregation): string
    {
        return $this->searchIndexFieldName;
    }
}
