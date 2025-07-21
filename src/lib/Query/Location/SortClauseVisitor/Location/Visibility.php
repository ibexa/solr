<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Location\SortClauseVisitor\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Solr\Query\SortClauseVisitor;

class Visibility extends SortClauseVisitor
{
    public function canVisit(SortClause $sortClause): bool
    {
        return $sortClause instanceof SortClause\Location\Visibility;
    }

    public function visit(SortClause $sortClause): string
    {
        return 'invisible_b' . $this->getDirection($sortClause);
    }
}
