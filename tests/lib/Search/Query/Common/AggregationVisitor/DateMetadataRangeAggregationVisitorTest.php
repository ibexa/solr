<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\Query\Common\AggregationVisitor;

use DateTime;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\DateMetadataRangeAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Range;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Solr\Query\Common\AggregationVisitor\DateMetadataRangeAggregationVisitor;

final class DateMetadataRangeAggregationVisitorTest extends AbstractAggregationVisitorTest
{
    protected function createVisitor(): AggregationVisitor
    {
        return new DateMetadataRangeAggregationVisitor();
    }

    public function dataProviderForCanVisit(): iterable
    {
        yield 'true' => [
             new DateMetadataRangeAggregation('foo', DateMetadataRangeAggregation::PUBLISHED, []),
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
        $ranges = [
            new Range(
                null,
                new DateTime('2018-01-01 00:00:00')
            ),
            new Range(
                new DateTime('2018-01-01 00:00:00'),
                new DateTime('2019-01-01 00:00:00')
            ),
            new Range(
                new DateTime('2019-01-01 00:00:00'),
                null
            ),
        ];

        yield DateMetadataRangeAggregation::PUBLISHED => [
            new DateMetadataRangeAggregation('typical', DateMetadataRangeAggregation::PUBLISHED, $ranges),
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'query',
                'q' => '*:*',
                'facet' => [
                    '*_2018-01-01T00:00:00Z' => [
                        'type' => 'query',
                        'q' => 'content_publication_date_dt:[* TO 2018-01-01T00:00:00Z}',
                    ],
                    '2018-01-01T00:00:00Z_2019-01-01T00:00:00Z' => [
                        'type' => 'query',
                        'q' => 'content_publication_date_dt:[2018-01-01T00:00:00Z TO 2019-01-01T00:00:00Z}',
                    ],
                    '2019-01-01T00:00:00Z_*' => [
                        'type' => 'query',
                        'q' => 'content_publication_date_dt:[2019-01-01T00:00:00Z TO *}',
                    ],
                ],
            ],
        ];

        yield DateMetadataRangeAggregation::MODIFIED => [
            new DateMetadataRangeAggregation('typical', DateMetadataRangeAggregation::MODIFIED, $ranges),
            self::EXAMPLE_LANGUAGE_FILTER,
            [
                'type' => 'query',
                'q' => '*:*',
                'facet' => [
                    '*_2018-01-01T00:00:00Z' => [
                        'type' => 'query',
                        'q' => 'content_modification_date_dt:[* TO 2018-01-01T00:00:00Z}',
                    ],
                    '2018-01-01T00:00:00Z_2019-01-01T00:00:00Z' => [
                        'type' => 'query',
                        'q' => 'content_modification_date_dt:[2018-01-01T00:00:00Z TO 2019-01-01T00:00:00Z}',
                    ],
                    '2019-01-01T00:00:00Z_*' => [
                        'type' => 'query',
                        'q' => 'content_modification_date_dt:[2019-01-01T00:00:00Z TO *}',
                    ],
                ],
            ],
        ];
    }
}

class_alias(DateMetadataRangeAggregationVisitorTest::class, 'EzSystems\EzPlatformSolrSearchEngine\Tests\Search\Query\Common\AggregationVisitor\DateMetadataRangeAggregationVisitorTest');
