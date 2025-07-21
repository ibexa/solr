<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractAggregationVisitorTest extends TestCase
{
    protected const array EXAMPLE_LANGUAGE_FILTER = [
        'languages' => ['eng-GB'],
    ];

    protected AggregationVisitor $visitor;

    protected AggregationVisitor&MockObject $dispatcherVisitor;

    protected function setUp(): void
    {
        $this->visitor = $this->createVisitor();
        $this->dispatcherVisitor = $this->createMock(AggregationVisitor::class);
    }

    abstract protected function createVisitor(): AggregationVisitor;

    /**
     * @param array{languages?: string[], languageCode?: string, useAlwaysAvailable?: bool} $languageFilter
     *
     * @dataProvider dataProviderForCanVisit
     */
    final public function testCanVisit(
        Aggregation $aggregation,
        array $languageFilter,
        bool $expectedValue
    ): void {
        self::assertEquals(
            $expectedValue,
            $this->visitor->canVisit($aggregation, $languageFilter)
        );
    }

    /**
     * @return iterable<string, array{
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation,
     *     1: array{languages: string[]},
     *     2: bool
     * }>
     */
    abstract public function dataProviderForCanVisit(): iterable;

    /**
     * @param array{languages: string[]} $languageFilter
     * @param array<mixed> $expectedResult
     *
     * @dataProvider dataProviderForVisit
     */
    final public function testVisit(
        Aggregation $aggregation,
        array $languageFilter,
        array $expectedResult
    ): void {
        $this->configureMocksForTestVisit($aggregation, $languageFilter, $expectedResult);

        self::assertEquals(
            $expectedResult,
            $this->visitor->visit($this->dispatcherVisitor, $aggregation, $languageFilter)
        );
    }

    /**
     * @return iterable<string, array<mixed>>
     */
    abstract public function dataProviderForVisit(): iterable;

    /**
     * @param array{languages: string[]} $languageFilter
     * @param array<mixed> $expectedResult
     */
    protected function configureMocksForTestVisit(
        Aggregation $aggregation,
        array $languageFilter,
        array $expectedResult
    ): void {
        // Overwrite in parent class to configure additional mocks
    }
}
