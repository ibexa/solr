<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractRangeAggregationKeyMapperTest extends TestCase
{
    protected const array EXAMPLE_LANGUAGE_FILTER = [];

    /**
     * @dataProvider dataProviderForTestMap
     *
     * @param array{} $languageFilter
     */
    final public function testMap(Aggregation $aggregation, array $languageFilter, string $key, mixed $expectedResult): void
    {
        $mapper = $this->createRangeAggregationKeyMapper();

        self::assertEquals(
            $expectedResult,
            $mapper->map($aggregation, $languageFilter, $key)
        );
    }

    /**
     * @return iterable<string, array{
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation&\PHPUnit\Framework\MockObject\MockObject,
     *     1: array{},
     *     2: string,
     *     3: mixed,
     * }>
     */
    abstract public function dataProviderForTestMap(): iterable;

    abstract protected function createRangeAggregationKeyMapper(): RangeAggregationKeyMapper;

    protected function createAggregationMock(): Aggregation&MockObject
    {
        return $this->createMock(Aggregation::class);
    }
}
