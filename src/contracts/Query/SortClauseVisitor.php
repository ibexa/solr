<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Contracts\Solr\Query;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;

/**
 * Visits the sort clause into a Solr query.
 */
abstract class SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sort clause.
     */
    abstract public function canVisit(SortClause $sortClause): bool;

    /**
     * Map field value to a proper Solr representation.
     */
    abstract public function visit(SortClause $sortClause): string;

    /**
     * Get solr sort direction for sort clause.
     */
    protected function getDirection(SortClause $sortClause): string
    {
        return ' ' . ($sortClause->direction === 'descending' ? 'desc' : 'asc');
    }
}
