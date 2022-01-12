<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\SortClauseVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Solr\Query\SortClauseVisitor;

final class ContentTranslatedName extends SortClauseVisitor
{
    public function canVisit(SortClause $sortClause): bool
    {
        return $sortClause instanceof SortClause\ContentTranslatedName;
    }

    public function visit(SortClause $sortClause): string
    {
        return 'meta_content__name_s' . $this->getDirection($sortClause);
    }
}

class_alias(ContentTranslatedName::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\SortClauseVisitor\ContentTranslatedName');
