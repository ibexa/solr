<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use PHPUnit\Framework\TestCase;

abstract class AbstractAggregationVisitorTest extends TestCase
{
    protected const EXAMPLE_LANGUAGE_FILTER = [
        'languages' => ['eng-gb'],
    ];

    /** @var \Ibexa\Contracts\Solr\Query\AggregationVisitor */
    protected $visitor;

    /** @var \Ibexa\Contracts\Solr\Query\AggregationVisitor|\PHPUnit\Framework\MockObject\MockObject */
    protected $dispatcherVisitor;

    protected function setUp(): void
    {
        $this->visitor = $this->createVisitor();
        $this->dispatcherVisitor = $this->createMock(AggregationVisitor::class);
    }

    abstract protected function createVisitor(): AggregationVisitor;

    /**
     * @param array{languages: string[]} $languageFilter
     *
     * @dataProvider dataProviderForCanVisit
     */
    final public function testCanVisit(
        Aggregation $aggregation,
        array $languageFilter,
        bool $expectedValue
    ): void {
        $this->assertEquals(
            $expectedValue,
            $this->visitor->canVisit($aggregation, $languageFilter)
        );
    }

    /**
     * @return iterable<array{
     *     \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation,
     *     array{languages: string[]},
     *     bool,
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

        $this->assertEquals(
            $expectedResult,
            $this->visitor->visit($this->dispatcherVisitor, $aggregation, $languageFilter)
        );
    }

    /**
     * @return iterable<array{
     *     \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation,
     *     array{languages: string[]},
     *     array<mixed>,
     * }>
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

class_alias(AbstractAggregationVisitorTest::class, 'EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\Common\AggregationVisitor\AbstractAggregationVisitorTest');
