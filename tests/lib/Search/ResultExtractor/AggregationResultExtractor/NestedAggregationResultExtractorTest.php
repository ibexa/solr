<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\NestedAggregationResultExtractor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NestedAggregationResultExtractorTest extends TestCase
{
    private const EXAMPLE_NESTED_RESULT_KEY = 'foo';

    private AggregationResultExtractor&MockObject $innerResultExtractor;

    private NestedAggregationResultExtractor $resultExtractor;

    protected function setUp(): void
    {
        $this->innerResultExtractor = $this->createMock(AggregationResultExtractor::class);
        $this->resultExtractor = new NestedAggregationResultExtractor(
            $this->innerResultExtractor,
            self::EXAMPLE_NESTED_RESULT_KEY
        );
    }

    public function testCanVisit(): void
    {
        $aggregation = $this->createMock(Aggregation::class);

        $this->innerResultExtractor
            ->expects(self::once())
            ->method('canVisit')
            ->with($aggregation, AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER)
            ->willReturn(true);

        self::assertTrue(
            $this->resultExtractor->canVisit(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER
            )
        );
    }

    public function testExtract(): void
    {
        $expectedResult = $this->createMock(AggregationResult::class);

        $data = new stdClass();
        $data->buckets = [/* Some data */];

        $aggregation = $this->createMock(Aggregation::class);

        $this->innerResultExtractor
            ->expects(self::once())
            ->method('extract')
            ->with(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                $data
            )
            ->willReturn($expectedResult);

        $wrappedData = new stdClass();
        $wrappedData->{self::EXAMPLE_NESTED_RESULT_KEY} = $data;

        self::assertEquals(
            $expectedResult,
            $this->resultExtractor->extract(
                $aggregation,
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                $wrappedData
            )
        );
    }
}
