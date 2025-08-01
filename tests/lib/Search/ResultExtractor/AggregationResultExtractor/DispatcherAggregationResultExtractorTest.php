<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\DispatcherAggregationResultExtractor;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DispatcherAggregationResultExtractorTest extends TestCase
{
    private const array EXAMPLE_LANGUAGE_FILTER = [];

    public function testSupportsReturnsTrue(): void
    {
        $aggregation = $this->createMock(Aggregation::class);

        $dispatcher = new DispatcherAggregationResultExtractor([
            $this->createExtractorMockWithCanVisit($aggregation, false),
            $this->createExtractorMockWithCanVisit($aggregation, true),
            $this->createExtractorMockWithCanVisit($aggregation, false),
        ]);

        self::assertTrue($dispatcher->canVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER));
    }

    public function testSupportsReturnsFalse(): void
    {
        $aggregation = $this->createMock(Aggregation::class);

        $dispatcher = new DispatcherAggregationResultExtractor([
            $this->createExtractorMockWithCanVisit($aggregation, false),
            $this->createExtractorMockWithCanVisit($aggregation, false),
            $this->createExtractorMockWithCanVisit($aggregation, false),
        ]);

        self::assertFalse($dispatcher->canVisit($aggregation, self::EXAMPLE_LANGUAGE_FILTER));
    }

    public function testExtract(): void
    {
        $aggregation = $this->createMock(Aggregation::class);
        $data = new stdClass();

        $extractorA = $this->createExtractorMockWithCanVisit($aggregation, false);
        $extractorB = $this->createExtractorMockWithCanVisit($aggregation, true);
        $extractorC = $this->createExtractorMockWithCanVisit($aggregation, false);

        $dispatcher = new DispatcherAggregationResultExtractor([$extractorA, $extractorB, $extractorC]);

        $expectedResult = $this->createMock(AggregationResult::class);

        $extractorB
            ->method('extract')
            ->with($aggregation, self::EXAMPLE_LANGUAGE_FILTER, $data)
            ->willReturn($expectedResult);

        self::assertEquals(
            $expectedResult,
            $dispatcher->extract($aggregation, self::EXAMPLE_LANGUAGE_FILTER, $data)
        );
    }

    public function testVisitThrowsNotImplementedException(): void
    {
        $this->expectException(NotImplementedException::class);
        $this->expectExceptionMessage('No result extractor available for aggregation: ');

        $aggregation = $this->createMock(Aggregation::class);

        $dispatcher = new DispatcherAggregationResultExtractor([
            $this->createExtractorMockWithCanVisit($aggregation, false),
            $this->createExtractorMockWithCanVisit($aggregation, false),
            $this->createExtractorMockWithCanVisit($aggregation, false),
        ]);

        $dispatcher->extract($aggregation, self::EXAMPLE_LANGUAGE_FILTER, new stdClass());
    }

    private function createExtractorMockWithCanVisit(
        Aggregation $aggregation,
        bool $supports
    ): AggregationResultExtractor {
        $extractor = $this->createMock(AggregationResultExtractor::class);
        $extractor->method('canVisit')->with($aggregation, self::EXAMPLE_LANGUAGE_FILTER)->willReturn($supports);

        return $extractor;
    }
}
