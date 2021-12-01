<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Solr\Query\Common\AggregationVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;

/**
 * Resolves search index field name used for aggregation.
 */
interface AggregationFieldResolver
{
    public function resolveTargetField(Aggregation $aggregation): string;
}

class_alias(AggregationFieldResolver::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\AggregationVisitor\AggregationFieldResolver');
