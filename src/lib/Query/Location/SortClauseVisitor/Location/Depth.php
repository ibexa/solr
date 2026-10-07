<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Location\SortClauseVisitor\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Solr\Query\SortClauseVisitor;

/**
 * Visits the sortClause tree into a Solr query.
 */
class Depth extends SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sortClause.
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\Location\Depth;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @return string
     */
    public function visit(SortClause $sortClause)
    {
        return 'depth_i' . $this->getDirection($sortClause);
    }
}

class_alias(Depth::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Location\SortClauseVisitor\Location\Depth');
