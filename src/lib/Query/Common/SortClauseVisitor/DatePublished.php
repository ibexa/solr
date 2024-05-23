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
class DatePublished extends SortClauseVisitor
{
    /**
     * Check if visitor is applicable to current sortClause.
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\DatePublished;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @return string
     */
    public function visit(SortClause $sortClause)
    {
        return 'content_publication_date_dt' . $this->getDirection($sortClause);
    }
}

class_alias(DatePublished::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\SortClauseVisitor\DatePublished');
