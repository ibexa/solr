<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\LocationAggregationKeyMapper;
use Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LocationAggregationKeyMapperTest extends TestCase
{
    private const array EXAMPLE_LOCATION_IDS = ['2', '54', '47'];

    private LocationService&MockObject $locationService;

    private LocationAggregationKeyMapper $mapper;

    protected function setUp(): void
    {
        $this->locationService = $this->createMock(LocationService::class);
        $this->mapper = new LocationAggregationKeyMapper($this->locationService);
    }

    public function testMap(): void
    {
        $expectedLocations = $this->createExpectedLocations();

        $this->locationService
            ->method('loadLocationList')
            ->with(self::EXAMPLE_LOCATION_IDS)
            ->willReturn($expectedLocations);

        self::assertEquals(
            array_combine(
                self::EXAMPLE_LOCATION_IDS,
                $expectedLocations
            ),
            $this->mapper->map(
                $this->createMock(Aggregation::class),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                self::EXAMPLE_LOCATION_IDS
            )
        );
    }

    /**
     * @return array<int, \Ibexa\Contracts\Core\Repository\Values\Content\Location>
     */
    private function createExpectedLocations(): array
    {
        $locations = [];
        foreach (self::EXAMPLE_LOCATION_IDS as $locationId) {
            $locationId = (int)$locationId;

            $location = $this->createMock(Location::class);
            $location->method('__get')->with('id')->willReturn($locationId);

            $locations[$locationId] = $location;
        }

        return $locations;
    }
}
