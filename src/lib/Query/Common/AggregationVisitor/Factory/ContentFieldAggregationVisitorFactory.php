<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor\Factory;

use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Core\Search\Common\FieldNameResolver;
use Ibexa\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver\ContentFieldAggregationFieldResolver;
use Ibexa\Solr\Query\Common\AggregationVisitor\RangeAggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\StatsAggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\TermAggregationVisitor;

final readonly class ContentFieldAggregationVisitorFactory
{
    public function __construct(
        private FieldNameResolver $fieldNameResolver
    ) {
    }

    public function createRangeAggregationVisitor(
        string $aggregationClass,
        string $searchIndexFieldName
    ): AggregationVisitor {
        return new RangeAggregationVisitor(
            $aggregationClass,
            new ContentFieldAggregationFieldResolver(
                $this->fieldNameResolver,
                $searchIndexFieldName
            )
        );
    }

    public function createStatsAggregationVisitor(
        string $aggregationClass,
        string $searchIndexFieldName
    ): AggregationVisitor {
        return new StatsAggregationVisitor(
            $aggregationClass,
            new ContentFieldAggregationFieldResolver(
                $this->fieldNameResolver,
                $searchIndexFieldName
            )
        );
    }

    public function createTermAggregationVisitor(
        string $aggregationClass,
        string $searchIndexFieldName
    ): AggregationVisitor {
        return new TermAggregationVisitor(
            $aggregationClass,
            new ContentFieldAggregationFieldResolver(
                $this->fieldNameResolver,
                $searchIndexFieldName
            )
        );
    }
}
