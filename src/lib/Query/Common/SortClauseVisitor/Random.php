<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\SortClauseVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Solr\Query\SortClauseVisitor;

/**
 * Visits the sortClause tree into a Solr query.
 */
class Random extends SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sortClause.
     */
    public function canVisit(SortClause $sortClause): bool
    {
        return $sortClause instanceof SortClause\Random;
    }

    /**
     * Map field value to a proper Solr representation.
     */
    public function visit(SortClause $sortClause): string
    {
        $seed = $sortClause->targetData->seed ?? mt_rand();

        return 'random_' . (string)$seed . ' ' . $this->getDirection($sortClause);
    }
}
