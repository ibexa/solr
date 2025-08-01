<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Contracts\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver;
use Ibexa\Solr\Query\Common\AggregationVisitor\TermAggregationVisitor;
use PHPUnit\Framework\MockObject\MockObject;

final class TermAggregationVisitorTest extends AbstractAggregationVisitorTest
{
    private AggregationFieldResolver&MockObject $aggregationFieldResolver;

    #[\Override]
    protected function setUp(): void
    {
        $this->aggregationFieldResolver = $this->createMock(AggregationFieldResolver::class);
        $this->aggregationFieldResolver
            ->method('resolveTargetField')
            ->with(self::isInstanceOf(AbstractTermAggregation::class))
            ->willReturn('custom_field_id');

        parent::setUp();
    }

    protected function createVisitor(): AggregationVisitor
    {
        return new TermAggregationVisitor(AbstractTermAggregation::class, $this->aggregationFieldResolver);
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
            $this->createMock(AbstractTermAggregation::class),
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
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation,
     *     1: array<string, list<string>>,
     *     2: array{
     *         type: string,
     *         field: string,
     *         limit: int,
     *         mincount: int
     *     }
     * }>
     */
    public function dataProviderForVisit(): iterable
    {
        $aggregation = $this->createMock(AbstractTermAggregation::class);
        $aggregation->method('getLimit')->willReturn(100);
        $aggregation->method('getMinCount')->willReturn(10);

        yield [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'terms',
                'field' => 'custom_field_id',
                'limit' => 100,
                'mincount' => 10,
            ],
        ];
    }
}
