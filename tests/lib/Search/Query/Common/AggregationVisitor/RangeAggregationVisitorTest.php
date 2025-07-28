<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Range;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Contracts\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver;
use Ibexa\Solr\Query\Common\AggregationVisitor\RangeAggregationVisitor;
use PHPUnit\Framework\MockObject\MockObject;

final class RangeAggregationVisitorTest extends AbstractAggregationVisitorTest
{
    private AggregationFieldResolver&MockObject $aggregationFieldResolver;

    #[\Override]
    protected function setUp(): void
    {
        $this->aggregationFieldResolver = $this->createMock(AggregationFieldResolver::class);
        $this->aggregationFieldResolver
            ->method('resolveTargetField')
            ->with(self::isInstanceOf(AbstractRangeAggregation::class))
            ->willReturn('custom_field_id');

        parent::setUp();
    }

    protected function createVisitor(): AggregationVisitor
    {
        return new RangeAggregationVisitor(AbstractRangeAggregation::class, $this->aggregationFieldResolver);
    }

    /**
     * @return iterable<string, array{
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation,
     *     1: array{languages: string[]},
     *     2: bool
     * }>
     */
    public function dataProviderForCanVisit(): iterable
    {
        yield 'true' => [
            $this->createMock(AbstractRangeAggregation::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            true,
        ];

        yield 'false' => [
            $this->createMock(Aggregation::class),
            self::EXAMPLE_LANGUAGE_FILTER,
            false,
        ];
    }

    /**
     * @return iterable<array{
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation,
     *     1: array{languages: string[]},
     *     2: array{
     *         type: string,
     *         q: string,
     *         facet: array<string, array{
     *             type: string,
     *             q: string
     *         }>
     *     }
     * }>
     */
    public function dataProviderForVisit(): iterable
    {
        $aggregation = $this->createMock(AbstractRangeAggregation::class);
        $aggregation->method('getRanges')->willReturn([
            new Range(null, 10),
            new Range(10, 100),
            new Range(100, null),
        ]);

        yield [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'query',
                'q' => '*:*',
                'facet' => [
                    '*_10' => [
                        'type' => 'query',
                        'q' => 'custom_field_id:[* TO 10}',
                    ],
                    '10_100' => [
                        'type' => 'query',
                        'q' => 'custom_field_id:[10 TO 100}',
                    ],
                    '100_*' => [
                        'type' => 'query',
                        'q' => 'custom_field_id:[100 TO *}',
                    ],
                ],
            ],
        ];
    }
}
