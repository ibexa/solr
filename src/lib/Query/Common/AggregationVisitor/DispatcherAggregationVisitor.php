<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Exceptions\NotImplementedException;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;

final class DispatcherAggregationVisitor implements AggregationVisitor
{
    /** @var iterable<\Ibexa\Contracts\Solr\Query\AggregationVisitor> */
    private $visitors;

    /**
     * @param iterable<\Ibexa\Contracts\Solr\Query\AggregationVisitor> $visitors
     */
    public function __construct(iterable $visitors)
    {
        $this->visitors = $visitors;
    }

    public function canVisit(Aggregation $aggregation, array $languageFilter): bool
    {
        return $this->findVisitor($aggregation, $languageFilter) !== null;
    }

    public function visit(
        AggregationVisitor $dispatcherVisitor,
        Aggregation $aggregation,
        array $languageFilter
    ): array {
        $visitor = $this->findVisitor($aggregation, $languageFilter);

        if ($visitor === null) {
            throw new NotImplementedException(
                'No visitor available for: ' . get_class($aggregation)
            );
        }

        return $visitor->visit($this, $aggregation, $languageFilter);
    }

    /**
     * @param array{languages: string[]} $languageFilter
     */
    private function findVisitor(Aggregation $aggregation, array $languageFilter): ?AggregationVisitor
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->canVisit($aggregation, $languageFilter)) {
                return $visitor;
            }
        }

        return null;
    }
}
