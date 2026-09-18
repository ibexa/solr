<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;

use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper\NullRangeAggregationKeyMapper;

final class NullRangeAggregationKeyMapperTest extends AbstractRangeAggregationKeyMapperTestCase
{
    /**
     * @return iterable<string, array{
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation&\PHPUnit\Framework\MockObject\Stub,
     *     1: array{},
     *     2: string,
     *     3: ?string,
     * }>
     */
    public static function dataProviderForTestMap(): iterable
    {
        yield 'null' => [
            self::createAggregationMock(),
            self::EXAMPLE_LANGUAGE_FILTER,
            '*',
            null,
        ];

        yield 'key' => [
            self::createAggregationMock(),
            self::EXAMPLE_LANGUAGE_FILTER,
            'foo',
            'foo',
        ];
    }

    protected function createRangeAggregationKeyMapper(): RangeAggregationKeyMapper
    {
        return new NullRangeAggregationKeyMapper();
    }
}
