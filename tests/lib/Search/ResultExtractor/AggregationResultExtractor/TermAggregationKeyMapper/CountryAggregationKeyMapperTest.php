<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Field\CountryTermAggregation;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\CountryAggregationKeyMapper;
use Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class CountryAggregationKeyMapperTest extends TestCase
{
    private const array EXAMPLE_RAW_KEYS = [93, 94, 55];

    /**
     * Example country info entries from "ibexa.field_type.country.data" parameter.
     */
    private const array EXAMPLE_COUNTRIES_INFO = [
        'AF' => [
            'Name' => 'Afghanistan',
            'Alpha2' => 'AF',
            'Alpha3' => 'AFG',
            'IDC' => '93',
        ],
        'AR' => [
            'Name' => 'Argentina',
            'Alpha2' => 'AR',
            'Alpha3' => 'ARG',
            'IDC' => '94',
        ],
        'BR' => [
            'Name' => 'Brazil',
            'Alpha2' => 'BR',
            'Alpha3' => 'BRA',
            'IDC' => '55',
        ],
    ];

    /**
     * @dataProvider dataProviderForTestMap
     *
     * @param array{languages: string[]} $languageFilter
     * @param list<int> $keys
     * @param array<int, string> $expectedResult
     */
    public function testMap(
        Aggregation $aggregation,
        array $languageFilter,
        array $keys,
        array $expectedResult
    ): void {
        $mapper = new CountryAggregationKeyMapper(self::EXAMPLE_COUNTRIES_INFO);

        self::assertEquals(
            $expectedResult,
            $mapper->map(
                $aggregation,
                $languageFilter,
                $keys
            )
        );
    }

    /**
     * @return iterable<string, array{
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Field\CountryTermAggregation,
     *     1: array{languageCode: string, useAlwaysAvailable: bool},
     *     2: list<int>,
     *     3: array<int, string>
     * }>
     */
    public function dataProviderForTestMap(): iterable
    {
        yield 'default' => [
            new CountryTermAggregation('aggregation', 'product', 'country'),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            self::EXAMPLE_RAW_KEYS,
            [
                93 => 'AFG',
                94 => 'ARG',
                55 => 'BRA',
            ],
        ];

        yield 'alpha2' => [
            new CountryTermAggregation('aggregation', 'product', 'country', CountryTermAggregation::TYPE_ALPHA_2),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            self::EXAMPLE_RAW_KEYS,
            [
                93 => 'AF',
                94 => 'AR',
                55 => 'BR',
            ],
        ];

        yield 'alpha3' => [
            new CountryTermAggregation('aggregation', 'product', 'country', CountryTermAggregation::TYPE_ALPHA_3),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            self::EXAMPLE_RAW_KEYS,
            [
                93 => 'AFG',
                94 => 'ARG',
                55 => 'BRA',
            ],
        ];

        yield 'name' => [
            new CountryTermAggregation('aggregation', 'product', 'country', CountryTermAggregation::TYPE_NAME),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            self::EXAMPLE_RAW_KEYS,
            [
                93 => 'Afghanistan',
                94 => 'Argentina',
                55 => 'Brazil',
            ],
        ];

        yield 'idc' => [
            new CountryTermAggregation('aggregation', 'product', 'country', CountryTermAggregation::TYPE_IDC),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            self::EXAMPLE_RAW_KEYS,
            [
                93 => '93',
                94 => '94',
                55 => '55',
            ],
        ];
    }
}
