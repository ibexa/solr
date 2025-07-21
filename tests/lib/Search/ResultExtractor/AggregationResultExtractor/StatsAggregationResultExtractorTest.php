<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractStatsAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\StatsAggregationResult;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\StatsAggregationResultExtractor;
use stdClass;

final class StatsAggregationResultExtractorTest extends AbstractAggregationResultExtractorTest
{
    protected function createExtractor(): AggregationResultExtractor
    {
        return new StatsAggregationResultExtractor(AbstractStatsAggregation::class);
    }

    /**
     * @return iterable<string, array{
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation,
     *     1: array{},
     *     2: bool
     * }>
     */
    public function dataProviderForTestCanVisit(): iterable
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

    /**
     * @return iterable<string, array{
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation,
     *     1: array{},
     *     2: \stdClass,
     *     3: \Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\StatsAggregationResult
     * }>
     */
    public function dataProviderForTestExtract(): iterable
    {
        $aggregation = $this->createMock(AbstractStatsAggregation::class);
        $aggregation->method('getName')->willReturn(self::EXAMPLE_AGGREGATION_NAME);

        yield 'defaults' => [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            $this->getEmptyRawData(),
            new StatsAggregationResult(
                self::EXAMPLE_AGGREGATION_NAME,
                null,
                null,
                null,
                null,
                null,
            ),
        ];

        yield 'typical' => [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            $this->getTypicalRawData(),
            new StatsAggregationResult(
                self::EXAMPLE_AGGREGATION_NAME,
                1000,
                0,
                125.0,
                100.0,
                1000.0
            ),
        ];
    }

    private function getEmptyRawData(): stdClass
    {
        $data = new stdClass();
        $data->count = null;
        $data->min = null;
        $data->max = null;
        $data->avg = null;
        $data->sum = null;

        return $data;
    }

    private function getTypicalRawData(): stdClass
    {
        $data = new stdClass();
        $data->count = 1000;
        $data->min = 0.0;
        $data->max = 125.0;
        $data->avg = 100.0;
        $data->sum = 1000.0;

        return $data;
    }
}
