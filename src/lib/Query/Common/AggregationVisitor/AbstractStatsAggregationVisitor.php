<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractStatsAggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;

abstract class AbstractStatsAggregationVisitor implements AggregationVisitor
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractStatsAggregation $aggregation
     */
    public function visit(
        AggregationVisitor $dispatcherVisitor,
        Aggregation $aggregation,
        array $languageFilter
    ): array {
        $field = $this->getTargetField($aggregation);

        return [
            'type' => 'query',
            'q' => $field . ':[* TO *]',
            'facet' => [
                'sum' => "sum($field)",
                'min' => "min($field)",
                'max' => "max($field)",
                'avg' => "avg($field)",
            ],
        ];
    }

    abstract protected function getTargetField(AbstractStatsAggregation $aggregation): string;
}

class_alias(AbstractStatsAggregationVisitor::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AbstractStatsAggregationVisitor');
