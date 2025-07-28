<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Location\SubtreeTermAggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;

final readonly class SubtreeTermAggregationVisitor implements AggregationVisitor
{
    public function __construct(
        private string $pathStringFieldName,
        private string $locationIdFieldName
    ) {
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $aggregation instanceof SubtreeTermAggregation;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\Location\SubtreeTermAggregation $aggregation
     */
    public function visit(
        AggregationVisitor $dispatcherVisitor,
        Aggregation $aggregation,
        array $languageFilter
    ): array {
        $pathString = $aggregation->getPathString();

        return [
            'type' => 'query',
            'q' => $this->pathStringFieldName . ':' . $this->getSubtreeWildcard($pathString),
            'facet' => [
                'nested' => [
                    'type' => 'terms',
                    'field' => $this->locationIdFieldName,
                    'limit' => $aggregation->getLimit() + $this->getPathLevel($pathString),
                    'mincount' => $aggregation->getMinCount(),
                ],
            ],
        ];
    }

    private function getSubtreeWildcard(string $pathString): string
    {
        return str_replace('/', '\\/', $pathString) . '?*';
    }

    private function getPathLevel(string $pathString): int
    {
        return count(explode('/', trim($pathString, '/')));
    }
}
