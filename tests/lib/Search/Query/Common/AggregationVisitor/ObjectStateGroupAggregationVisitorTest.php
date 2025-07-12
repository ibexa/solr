<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\ObjectStateTermAggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\ObjectStateAggregationVisitor;

final class ObjectStateGroupAggregationVisitorTest extends AbstractAggregationVisitorTest
{
    protected function createVisitor(): AggregationVisitor
    {
        return new ObjectStateAggregationVisitor();
    }

    public function dataProviderForCanVisit(): iterable
    {
        yield 'true' => [
            new ObjectStateTermAggregation('foo', 'ibexa_lock'),
            self::EXAMPLE_LANGUAGE_FILTER,
            true,
        ];

        yield 'false' => [
            $this->createMock(Aggregation::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            false,
        ];
    }

    public function dataProviderForVisit(): iterable
    {
        yield 'defaults' => [
            new ObjectStateTermAggregation('foo', 'ibexa_lock'),
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'terms',
                'field' => 'content_object_state_identifiers_ms',
                'prefix' => 'ibexa_lock:',
                'limit' => ObjectStateTermAggregation::DEFAULT_LIMIT,
                'mincount' => ObjectStateTermAggregation::DEFAULT_MIN_COUNT,
            ],
        ];

        $aggregation = new ObjectStateTermAggregation('foo', 'ibexa_lock');
        $aggregation->setLimit(100);
        $aggregation->setMinCount(10);

        yield 'custom' => [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'terms',
                'field' => 'content_object_state_identifiers_ms',
                'prefix' => 'ibexa_lock:',
                'limit' => 100,
                'mincount' => 10,
            ],
        ];
    }
}
