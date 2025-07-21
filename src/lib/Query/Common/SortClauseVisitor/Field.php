<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\SortClauseVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Solr\Query\SortClauseVisitor;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Search\Common\FieldNameResolver;

/**
 * Visits the sort clause into a Solr query.
 */
class Field extends SortClauseVisitor
{
    public function __construct(
        protected readonly FieldNameResolver $fieldNameResolver
    ) {
    }

    protected function getSortFieldName(
        SortClause $sortClause,
        string $contentTypeIdentifier,
        string $fieldDefinitionIdentifier
    ): ?string {
        return $this->fieldNameResolver->getSortFieldName(
            $sortClause,
            $contentTypeIdentifier,
            $fieldDefinitionIdentifier
        );
    }

    public function canVisit(SortClause $sortClause): bool
    {
        return $sortClause instanceof SortClause\Field;
    }

    /**
     * Map the $sortClause to a proper Solr representation.
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException If no sortable fields are found for the given sort clause target.
     */
    public function visit(SortClause $sortClause): string
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Target\FieldTarget $target */
        $target = $sortClause->targetData;
        $fieldName = $this->getSortFieldName(
            $sortClause,
            $target->typeIdentifier,
            $target->fieldIdentifier
        );

        if ($fieldName === null) {
            throw new InvalidArgumentException(
                '$sortClause->targetData',
                'No searchable Fields found for the provided Sort Clause target ' .
                "'{$target->fieldIdentifier}' on '{$target->typeIdentifier}'."
            );
        }

        return $fieldName . $this->getDirection($sortClause);
    }
}
