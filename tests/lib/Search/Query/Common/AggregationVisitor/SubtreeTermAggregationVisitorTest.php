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
    private const EXAMPLE_AGGREGATION_NAME = 'custom_aggregation';
    private const EXAMPLE_PATH_STRING = '/1/2/';

    private const EXAMPLE_PATH_STRING_FIELD_NAME = 'path_string_id';
    private const EXAMPLE_LOCATION_ID_FIELD_NAME = 'location_id_id';

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
