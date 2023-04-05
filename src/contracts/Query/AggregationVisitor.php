<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\Solr\Query;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation;

interface AggregationVisitor
{
    /**
     * Check if visitor is applicable to current aggreagtion.
     *
     * @phpstan-param array{languages: string[]} $languageFilter
     */
    public function canVisit(Aggregation $aggregation, array $languageFilter): bool;

    /**
     * @phpstan-param array{languages: string[]} $languageFilter
     *
     * @return string[]
     */
    public function visit(
        AggregationVisitor $dispatcherVisitor,
        Aggregation $aggregation,
        array $languageFilter
    ): array;
}

class_alias(AggregationVisitor::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\AggregationVisitor');
