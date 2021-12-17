<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;
use Ibexa\Contracts\Solr\Query\Common\AggregationVisitor\AggregationFieldResolver;

final class RawAggregationFieldResolver implements AggregationFieldResolver
{
    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation\RawAggregation $aggregation
     */
    public function resolveTargetField(Aggregation $aggregation): string
    {
        return $aggregation->getFieldName();
    }
}

class_alias(RawAggregationFieldResolver::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver\RawAggregationFieldResolver');
