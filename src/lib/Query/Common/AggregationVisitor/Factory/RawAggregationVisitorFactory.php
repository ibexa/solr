<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor\Factory;

use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver\RawAggregationFieldResolver;
use Ibexa\Solr\Query\Common\AggregationVisitor\RangeAggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\StatsAggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\TermAggregationVisitor;

final class RawAggregationVisitorFactory
{
    public function createRangeAggregationVisitor(
        string $aggregationClass
    ): AggregationVisitor {
        return new RangeAggregationVisitor(
            $aggregationClass,
            new RawAggregationFieldResolver()
        );
    }

    public function createStatsAggregationVisitor(
        string $aggregationClass
    ): AggregationVisitor {
        return new StatsAggregationVisitor(
            $aggregationClass,
            new RawAggregationFieldResolver()
        );
    }

    public function createTermAggregationVisitor(
        string $aggregationClass
    ): AggregationVisitor {
        return new TermAggregationVisitor(
            $aggregationClass,
            new RawAggregationFieldResolver()
        );
    }
}

class_alias(RawAggregationVisitorFactory::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\Factory\RawAggregationVisitorFactory');
