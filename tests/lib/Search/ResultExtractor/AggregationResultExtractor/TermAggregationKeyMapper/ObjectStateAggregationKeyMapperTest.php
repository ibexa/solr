<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\ObjectStateTermAggregation;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectState;
use Ibexa\Contracts\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper\ObjectStateAggregationKeyMapper;
use Ibexa\Tests\Solr\Search\ResultExtractor\AggregationResultExtractor\AggregationResultExtractorTestUtils;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ObjectStateAggregationKeyMapperTest extends TestCase
{
    private ObjectStateService&MockObject $objectStateService;

    private ObjectStateAggregationKeyMapper $mapper;

    protected function setUp(): void
    {
        $this->objectStateService = $this->createMock(ObjectStateService::class);
        $this->mapper = new ObjectStateAggregationKeyMapper($this->objectStateService);
    }

    public function testMap(): void
    {
        $expectedObjectStates = array_combine(
            ['ez_lock:unlocked', 'ez_lock:locked'],
            $this->configureObjectStateService('ez_lock', ['unlocked', 'locked'])
        );

        self::assertEquals(
            $expectedObjectStates,
            $this->mapper->map(
                new ObjectStateTermAggregation('aggregation', 'ez_lock'),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                ['ez_lock:unlocked', 'ez_lock:locked']
            )
        );
    }

    private function configureObjectStateService(
        string $objectStateGroupIdentifier,
        iterable $objectStateIdentifiers
    ): array {
        $objectStateGroup = $this->createMock(ObjectStateGroup::class);

        $this->objectStateService
            ->method('loadObjectStateGroupByIdentifier')
            ->with($objectStateGroupIdentifier)
            ->willReturn($objectStateGroup);

        $expectedObjectStates = [];
        foreach ($objectStateIdentifiers as $i => $objectStateIdentifier) {
            $objectState = $this->createMock(ObjectState::class);

            $this->objectStateService
                ->expects(self::at($i + 1))
                ->method('loadObjectStateByIdentifier')
                ->with($objectStateGroup, $objectStateIdentifier, [])
                ->willReturn($objectState);

            $expectedObjectStates[] = $objectState;
        }

        return $expectedObjectStates;
    }
}
