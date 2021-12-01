<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Query\Common\SortClauseVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Solr\Query\SortClauseVisitor;

final class CustomField extends SortClauseVisitor
{
    public function canVisit(SortClause $sortClause): bool
    {
        return $sortClause instanceof SortClause\CustomField;
    }

    public function visit(SortClause $sortClause): string
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Target\CustomFieldTarget $targetData */
        $targetData = $sortClause->targetData;

        return $targetData->fieldName . ' ' . $this->getDirection($sortClause);
    }
}

class_alias(CustomField::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\SortClauseVisitor\CustomField');
