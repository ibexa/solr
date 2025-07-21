<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Location\SubtreeTermAggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\SubtreeTermAggregationVisitor;

final class SubtreeTermAggregationVisitorTest extends AbstractAggregationVisitorTest
{
    private const string EXAMPLE_AGGREGATION_NAME = 'custom_aggregation';
    private const string EXAMPLE_PATH_STRING = '/1/2/';

    private const string EXAMPLE_PATH_STRING_FIELD_NAME = 'path_string_id';
    private const string EXAMPLE_LOCATION_ID_FIELD_NAME = 'location_id_id';

    /**
     * @return iterable<string, array{
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation,
     *     1: array{languages: string[]},
     *     2: bool
     * }>
     */
    public function dataProviderForCanVisit(): iterable
    {
        yield 'true' => [
            new SubtreeTermAggregation(
                self::EXAMPLE_AGGREGATION_NAME,
                self::EXAMPLE_PATH_STRING
            ),
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
     * @return iterable<array{
     *     0: \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Location\SubtreeTermAggregation,
     *     1: array{languages: string[]},
     *     2: array{
     *         type: string,
     *         q: string,
     *         facet: array{
     *             nested: array{
     *                 type: string,
     *                 field: string,
     *                 limit: int,
     *                 mincount: int
     *             }
     *         }
     *     }
     * }>
     */
    public function dataProviderForVisit(): iterable
    {
        yield [
            new SubtreeTermAggregation(
                self::EXAMPLE_AGGREGATION_NAME,
                self::EXAMPLE_PATH_STRING
            ),
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'query',
                'q' => 'path_string_id:\/1\/2\/?*',
                'facet' => [
                    'nested' => [
                        'type' => 'terms',
                        'field' => 'location_id_id',
                        'limit' => 12,
                        'mincount' => 1,
                    ],
                ],
            ],
        ];
    }

    protected function createVisitor(): AggregationVisitor
    {
        return new SubtreeTermAggregationVisitor(
            self::EXAMPLE_PATH_STRING_FIELD_NAME,
            self::EXAMPLE_LOCATION_ID_FIELD_NAME
        );
    }
}
