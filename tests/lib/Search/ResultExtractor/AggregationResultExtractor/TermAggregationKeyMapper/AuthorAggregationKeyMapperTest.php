<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Field\AuthorTermAggregation;
use Ibexa\Core\FieldType\Author\Author;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\AuthorAggregationKeyMapper;
use Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\TestCase;

final class AuthorAggregationKeyMapperTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestMap
     *
     * @param array{languages: string[]} $languageFilter
     * @param list<string|int> $keys
     * @param array<string, \Ibexa\Core\FieldType\Author\Author> $expectedResult
     */
    public function testMap(
        Aggregation $aggregation,
        array $languageFilter,
        array $keys,
        array $expectedResult
    ): void {
        $mapper = new AuthorAggregationKeyMapper();

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
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Field\AuthorTermAggregation,
     *     1: array<string, mixed>,
     *     2: list<string>,
     *     3: array<string, \Ibexa\Core\FieldType\Author\Author>
     * }>
     */
    public function dataProviderForTestMap(): iterable
    {
        $input = [
            '{"name":"Boba Fett","email":"boba.fett@example.com"}',
            '{"name":"Leia Organa","email":"leia.organa@example.com"}',
            '{"name":"Luke Skywalker","email":"luke.skywalker@example.com"}',
        ];

        $output = [
            new Author([
                'name' => 'Boba Fett',
                'email' => 'boba.fett@example.com',
            ]),
            new Author([
                'name' => 'Leia Organa',
                'email' => 'leia.organa@example.com',
            ]),
            new Author([
                'name' => 'Luke Skywalker',
                'email' => 'luke.skywalker@example.com',
            ]),
        ];

        yield 'default' => [
            new AuthorTermAggregation('example_aggregation', 'article', 'author'),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            $input,
            array_combine($input, $output),
        ];

        yield 'skip on decode error' => [
            new AuthorTermAggregation('example_aggregation', 'article', 'author'),
            AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
            $input + [
                'INVALID_JSON',
            ],
            array_combine($input, $output),
        ];
    }
}
