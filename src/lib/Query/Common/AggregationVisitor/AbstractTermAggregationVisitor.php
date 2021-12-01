<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;

abstract class AbstractTermAggregationVisitor implements AggregationVisitor
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\AbstractTermAggregation $aggregation
     */
    public function visit(
        AggregationVisitor $dispatcherVisitor,
        Aggregation $aggregation,
        array $languageFilter
    ): array {
        return [
            'type' => 'terms',
            'field' => $this->getTargetField($aggregation),
            'limit' => $aggregation->getLimit(),
            'mincount' => $aggregation->getMinCount(),
        ];
    }

    abstract protected function getTargetField(AbstractTermAggregation $aggregation): string;
}

class_alias(AbstractTermAggregationVisitor::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AbstractTermAggregationVisitor');
