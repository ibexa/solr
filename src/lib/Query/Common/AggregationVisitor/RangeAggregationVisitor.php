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

final class RangeAggregationVisitor extends AbstractRangeAggregationVisitor
{
    /** @var string */
    private $aggregationClass;

    /** @var \Ibexa\Contracts\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver */
    private $aggregationFieldResolver;

    public function __construct(string $aggregationClass, AggregationFieldResolver $aggregationFieldResolver)
    {
        $this->aggregationClass = $aggregationClass;
        $this->aggregationFieldResolver = $aggregationFieldResolver;
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof $this->aggregationClass;
    }

    protected function getTargetField(AbstractRangeAggregation $aggregation): string
    {
        return $this->aggregationFieldResolver->resolveTargetField($aggregation);
    }
}
