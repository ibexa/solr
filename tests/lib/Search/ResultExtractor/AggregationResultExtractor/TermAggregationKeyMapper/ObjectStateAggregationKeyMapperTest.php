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
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
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
            ['ibexa_lock:unlocked', 'ibexa_lock:locked'],
            $this->configureObjectStateService('ibexa_lock', ['unlocked', 'locked'])
        );

        self::assertEquals(
            $expectedObjectStates,
            $this->mapper->map(
                new ObjectStateTermAggregation('aggregation', 'ibexa_lock'),
                AggregationResultExtractorTestUtils::EXAMPLE_LANGUAGE_FILTER,
                ['ibexa_lock:unlocked', 'ibexa_lock:locked']
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
        $map = [];
        foreach ($objectStateIdentifiers as $objectStateIdentifier) {
            $objectState = $this->createMock(ObjectState::class);
            $expectedObjectStates[] = $objectState;
            $map[$objectStateIdentifier] = $objectState;
        }

        $this->objectStateService
            ->method('loadObjectStateByIdentifier')
            ->willReturnCallback(
                static function ($group, $identifier, $options) use ($objectStateGroup, $map) {
                    if ($group === $objectStateGroup && isset($map[$identifier]) && $options === []) {
                        return $map[$identifier];
                    }

                    throw new InvalidArgumentException('identifier', "Unexpected arguments: $identifier");
                }
            );

        return $expectedObjectStates;
    }
}
