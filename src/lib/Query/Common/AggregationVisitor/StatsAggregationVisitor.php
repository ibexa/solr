<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractStatsAggregation;
use Ibexa\Contracts\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver;

final class StatsAggregationVisitor extends AbstractStatsAggregationVisitor
{
    public function __construct(
        private readonly string $aggregationClass,
        private readonly AggregationFieldResolver $aggregationFieldResolver
    ) {
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof $this->aggregationClass;
    }

    protected function getTargetField(AbstractStatsAggregation $aggregation): string
    {
        return $this->aggregationFieldResolver->resolveTargetField($aggregation);
    }
}
