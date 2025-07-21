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
class ContentName extends SortClauseVisitor
{
    public function canVisit(SortClause $sortClause): bool
    {
        return $sortClause instanceof SortClause\ContentName;
    }

    public function visit(SortClause $sortClause): string
    {
        return 'content_name_s' . $this->getDirection($sortClause);
    }
}
