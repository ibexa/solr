<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResultEntry;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationResultExtractor;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

final class TermAggregationResultExtractorTest extends AbstractAggregationResultExtractorTest
{
    private TermAggregationKeyMapper&MockObject $keyMapper;

    #[\Override]
    protected function setUp(): void
    {
        $this->keyMapper = $this->createMock(TermAggregationKeyMapper::class);
        $this->keyMapper
            ->method('map')
            ->willReturnCallback(static fn (Aggregation $aggregation, array $languageFilter, array $keys): array => array_combine($keys, array_map('strtoupper', $keys)));

        $this->extractor = $this->createExtractor();
    }

    protected function createExtractor(): AggregationResultExtractor
    {
        return new TermAggregationResultExtractor(
            AbstractTermAggregation::class,
            $this->keyMapper,
        );
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
     * @return iterable<string, array{
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation,
     *     1: array{},
     *     2: \stdClass,
     *     3: \Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResult\TermAggregationResult
     * }>
     */
    public function dataProviderForTestExtract(): iterable
    {
        $aggregation = $this->createMock(AbstractTermAggregation::class);
        $aggregation->method('getName')->willReturn(self::EXAMPLE_AGGREGATION_NAME);

        yield 'defaults' => [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            $this->createEmptyRawData(),
            new TermAggregationResult(self::EXAMPLE_AGGREGATION_NAME, []),
        ];

        yield 'typical' => [
            $aggregation,
            self::EXAMPLE_LANGUAGE_FILTER,
            $this->createTypicalRawData(),
            new TermAggregationResult(
                self::EXAMPLE_AGGREGATION_NAME,
                [
                    new TermAggregationResultEntry('FOO', 10),
                    new TermAggregationResultEntry('BAR', 100),
                    new TermAggregationResultEntry('BAZ', 1000),
                ]
            ),
        ];
    }

    private function createEmptyRawData(): stdClass
    {
        $data = new stdClass();
        $data->buckets = [];

        return $data;
    }

    private function createTypicalRawData(): stdClass
    {
        $data = new stdClass();
        $data->buckets = [];
        $data->buckets[] = $this->createRawBucket('foo', 10);
        $data->buckets[] = $this->createRawBucket('bar', 100);
        $data->buckets[] = $this->createRawBucket('baz', 1000);

        return $data;
    }

    private function createRawBucket(string $val, int $count): stdClass
    {
        $bucket = new stdClass();
        $bucket->val = $val;
        $bucket->count = $count;

        return $bucket;
    }
}
