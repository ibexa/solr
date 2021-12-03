<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor;

use DateTimeInterface;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;

abstract class AbstractRangeAggregationVisitor implements AggregationVisitor
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractRangeAggregation $aggregation
     */
    public function visit(
        AggregationVisitor $dispatcherVisitor,
        Aggregation $aggregation,
        array $languageFilter
    ): array {
        $field = $this->getTargetField($aggregation);

        $rangeFacets = [];
        foreach ($aggregation->getRanges() as $range) {
            $from = $this->formatRangeValue($range->getFrom());
            $to = $this->formatRangeValue($range->getTo());

            $rangeFacets["${from}_${to}"] = [
                'type' => 'query',
                'q' => sprintf('%s:[%s TO %s}', $field, $from, $to),
            ];
        }

        return [
            'type' => 'query',
            'q' => '*:*',
            'facet' => $rangeFacets,
        ];
    }

    abstract protected function getTargetField(AbstractRangeAggregation $aggregation): string;

    private function formatRangeValue($value): string
    {
        if ($value === null) {
            return '*';
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d\\TH:i:s\\Z');
        }

        return (string)$value;
    }
}

class_alias(AbstractRangeAggregationVisitor::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AbstractRangeAggregationVisitor');
