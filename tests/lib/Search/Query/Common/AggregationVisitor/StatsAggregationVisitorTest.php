<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractStatsAggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Contracts\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver;
use Ibexa\Solr\Query\Common\AggregationVisitor\StatsAggregationVisitor;
use PHPUnit\Framework\MockObject\MockObject;

final class StatsAggregationVisitorTest extends AbstractAggregationVisitorTest
{
    /** @var \Ibexa\Contracts\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver|\PHPUnit\Framework\MockObject\MockObject */
    private MockObject $aggregationFieldResolver;

    protected function setUp(): void
    {
        $this->aggregationFieldResolver = $this->createMock(AggregationFieldResolver::class);
        $this->aggregationFieldResolver
            ->method('resolveTargetField')
            ->with(self::isInstanceOf(AbstractStatsAggregation::class))
            ->willReturn('custom_field_id');

        parent::setUp();
    }

    protected function createVisitor(): AggregationVisitor
    {
        return new StatsAggregationVisitor(AbstractStatsAggregation::class, $this->aggregationFieldResolver);
    }

    public function dataProviderForCanVisit(): iterable
    {
        yield 'true' => [
            $this->createMock(AbstractStatsAggregation::class),
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
        yield [
            $this->createMock(AbstractStatsAggregation::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'query',
                'q' => 'custom_field_id:[* TO *]',
                'facet' => [
                    'sum' => 'sum(custom_field_id)',
                    'min' => 'min(custom_field_id)',
                    'max' => 'max(custom_field_id)',
                    'avg' => 'avg(custom_field_id)',
                ],
            ],
        ];
    }
}
