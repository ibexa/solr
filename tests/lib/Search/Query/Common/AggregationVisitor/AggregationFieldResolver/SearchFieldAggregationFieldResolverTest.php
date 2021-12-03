<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\AggregationVisitor\AggregationFieldResolver;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver\SearchFieldAggregationFieldResolver;
use PHPUnit\Framework\TestCase;

final class SearchFieldAggregationFieldResolverTest extends TestCase
{
    public function testResolveTargetField(): void
    {
        $aggregation = $this->createMock(Aggregation::class);

        $aggregationFieldResolver = new SearchFieldAggregationFieldResolver('custom_field_id');

        $this->assertEquals(
            'custom_field_id',
            $aggregationFieldResolver->resolveTargetField($aggregation)
        );
    }
}

class_alias(SearchFieldAggregationFieldResolverTest::class, 'EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\Common\AggregationVisitor\AggregationFieldResolver\SearchFieldAggregationFieldResolverTest');
