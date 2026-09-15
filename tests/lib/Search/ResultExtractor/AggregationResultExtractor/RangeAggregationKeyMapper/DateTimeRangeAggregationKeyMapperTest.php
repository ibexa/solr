<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;

use DateTimeImmutable;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\RangeAggregationKeyMapper\DateTimeRangeAggregationKeyMapper;

final class DateTimeRangeAggregationKeyMapperTest extends AbstractRangeAggregationKeyMapperTestCase
{
    public static function dataProviderForTestMap(): iterable
    {
        yield 'null' => [
            self::createAggregationMock(),
            self::EXAMPLE_LANGUAGE_FILTER,
            '*',
            null,
        ];

        yield 'date string' => [
            self::createAggregationMock(),
            self::EXAMPLE_LANGUAGE_FILTER,
            '2020-01-01T00:00:00Z',
            new DateTimeImmutable('2020-01-01T00:00:00Z'),
        ];
    }

    protected function createRangeAggregationKeyMapper(): RangeAggregationKeyMapper
    {
        return new DateTimeRangeAggregationKeyMapper();
    }
}
