<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final readonly class ObjectStateAggregationKeyMapper implements TermAggregationKeyMapper
{
    public function __construct(
        private ObjectStateService $objectStateService
    ) {
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\ObjectStateTermAggregation $aggregation
     */
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $objectStateGroup = $this->objectStateService->loadObjectStateGroupByIdentifier(
            $aggregation->getObjectStateGroupIdentifier()
        );

        $mapped = [];
        foreach ($keys as $key) {
            [, $stateIdentifier] = explode(':', (string) $key, 2);

            try {
                $mapped[$key] = $this->objectStateService->loadObjectStateByIdentifier(
                    $objectStateGroup,
                    $stateIdentifier
                );
            } catch (NotFoundException) {
                // Skip non-existing object states
            }
        }

        return $mapped;
    }
}
