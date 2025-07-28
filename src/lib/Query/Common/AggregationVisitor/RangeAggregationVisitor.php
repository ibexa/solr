<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation;
use Ibexa\Contracts\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver;

/**
 * @phpstan-template TAggregation of \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation
 *
 * @phpstan-extends \Ibexa\Solr\Query\Common\AggregationVisitor\AbstractRangeAggregationVisitor<TAggregation>
 */
final class RangeAggregationVisitor extends AbstractRangeAggregationVisitor
{
    public function __construct(
        private readonly string $aggregationClass,
        private readonly AggregationFieldResolver $aggregationFieldResolver
    ) {
    }

    /**
     * @phpstan-param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation<TAggregation> $aggregation
     */
    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof $this->aggregationClass;
    }

    /**
     * @phpstan-param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation<TAggregation> $aggregation
     */
    protected function getTargetField(AbstractRangeAggregation $aggregation): string
    {
        return $this->aggregationFieldResolver->resolveTargetField($aggregation);
    }
}
