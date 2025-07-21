<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor\TermAggregationKeyMapper;

final readonly class SectionAggregationKeyMapper implements TermAggregationKeyMapper
{
    public function __construct(
        private SectionService $sectionService
    ) {
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\SectionTermAggregation $aggregation
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Section[]
     */
    public function map(Aggregation $aggregation, array $languageFilter, array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            try {
                $result[$key] = $this->sectionService->loadSection((int)$key);
            } catch (NotFoundException | UnauthorizedException) {
                // Skip missing section
            }
        }

        return $result;
    }
}
