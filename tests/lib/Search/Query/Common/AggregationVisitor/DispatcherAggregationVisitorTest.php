<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\DispatcherAggregationVisitor;
use Ibexa\Tests\Solr\Search\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class DispatcherAggregationVisitorTest extends TestCase
{
    private const EXAMPLE_LANGUAGE_FILTER = [
        'languages' => ['eng-GB'],
    ];

    private const EXAMPLE_VISITOR_RESULT = [
        'type' => 'terms',
        'field' => 'foo',
        'limit' => 100,
        'mincount' => 10,
    ];

    public function testCanVisitOnSupportedAggregation(): void
    {
        $aggregation = $this->createMock(Aggregation::class);

        $dispatcher = new DispatcherAggregationVisitor([
            $this->createVisitorMock($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
            $this->createVisitorMock($aggregation, self::EXAMPLE_LANGUAGE_FILTER, true),
            $this->createVisitorMock($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
        ]);

        self::assertTrue($dispatcher->canVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER));
    }

    public function testCanVisitOnNonSupportedAggregation(): void
    {
        $aggregation = $this->createMock(Aggregation::class);

        $dispatcher = new DispatcherAggregationVisitor([
            $this->createVisitorMock($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
            $this->createVisitorMock($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
            $this->createVisitorMock($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false),
        ]);

        self::assertFalse($dispatcher->canVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER));
    }

    public function testVisit(): void
    {
        $aggregation = $this->createMock(Aggregation::class);

        $visitorA = $this->createVisitorMock($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false);
        $visitorB = $this->createVisitorMock($aggregation, self::EXAMPLE_LANGUAGE_FILTER, true);
        $visitorC = $this->createVisitorMock($aggregation, self::EXAMPLE_LANGUAGE_FILTER, false);

        $dispatcher = new DispatcherAggregationVisitor([$visitorA, $visitorB, $visitorC]);

        $visitorB
            ->method('visit')
            ->with($dispatcher, $aggregation, self::EXAMPLE_LANGUAGE_FILTER)
            ->willReturn(self::EXAMPLE_VISITOR_RESULT);

        self::assertEquals(
            self::EXAMPLE_VISITOR_RESULT,
            $dispatcher->visit($dispatcher, $aggregation, self::EXAMPLE_LANGUAGE_FILTER)
        );
    }

    /**
     * @param array{languages: string[]} $languageFilter
     *
     * @return \PHPUnit\Framework\MockObject\MockObject&\Ibexa\Contracts\Solr\Query\AggregationVisitor
     */
    private function createVisitorMock(
        Aggregation $aggregation,
        array $languageFilter,
        bool $supports
    ): MockObject {
        $visitor = $this->createMock(AggregationVisitor::class);
        $visitor->method('canVisit')->with($aggregation, $languageFilter)->willReturn($supports);

        return $visitor;
    }
}
