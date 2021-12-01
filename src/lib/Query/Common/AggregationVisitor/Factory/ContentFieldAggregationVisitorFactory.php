<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor\Factory;

use Ibexa\Core\Search\Common\FieldNameResolver;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver\ContentFieldAggregationFieldResolver;
use Ibexa\Solr\Query\Common\AggregationVisitor\RangeAggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\StatsAggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\TermAggregationVisitor;

final class ContentFieldAggregationVisitorFactory
{
    /** @var \Ibexa\Core\Search\Common\FieldNameResolver */
    private $fieldNameResolver;

    public function __construct(FieldNameResolver $fieldNameResolver)
    {
        $this->fieldNameResolver = $fieldNameResolver;
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

class_alias(ContentFieldAggregationVisitorFactory::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\Factory\ContentFieldAggregationVisitorFactory');
