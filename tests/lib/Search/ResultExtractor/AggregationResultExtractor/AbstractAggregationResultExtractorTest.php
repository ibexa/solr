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
use PHPUnit\Framework\TestCase;
use stdClass;

abstract class AbstractAggregationResultExtractorTest extends TestCase
{
    protected const string EXAMPLE_AGGREGATION_NAME = 'custom_aggregation';
    protected const array EXAMPLE_LANGUAGE_FILTER = [];

    protected AggregationResultExtractor $extractor;

    protected function setUp(): void
    {
        $this->extractor = $this->createExtractor();
    }

    abstract protected function createExtractor(): AggregationResultExtractor;

    /**
     * @dataProvider dataProviderForTestCanVisit
     *
     * @param array{languages: string[]} $languageFilter
     */
    public function testCanVisit(
        Aggregation $aggregation,
        array $languageFilter,
        bool $expectedResult
    ): void {
        self::assertEquals(
            $expectedResult,
            $this->extractor->canVisit($aggregation, $languageFilter)
        );
    }

    abstract public function dataProviderForTestCanVisit(): iterable;

    /**
     * @dataProvider dataProviderForTestExtract
     *
     * @param array{languages: string[]} $languageFilter
     */
    public function testExtract(
        Aggregation $aggregation,
        array $languageFilter,
        stdClass $rawData,
        AggregationResult $expectedResult
    ): void {
        self::assertEquals(
            $expectedResult,
            $this->extractor->extract($aggregation, $languageFilter, $rawData)
        );
    }

    abstract public function dataProviderForTestExtract(): iterable;
}
