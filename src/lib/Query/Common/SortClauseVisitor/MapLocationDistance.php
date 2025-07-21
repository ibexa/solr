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
 * Visits the sortClause tree into a Solr query.
 */
class MapLocationDistance extends SortClauseVisitor
{
    public function __construct(
        protected readonly FieldNameResolver $fieldNameResolver,
        protected string $fieldName
    ) {
    }

    protected function getSortFieldName(
        SortClause $sortClause,
        string $contentTypeIdentifier,
        string $fieldDefinitionIdentifier,
        ?string $name = null
    ): ?string {
        return $this->fieldNameResolver->getSortFieldName(
            $sortClause,
            $contentTypeIdentifier,
            $fieldDefinitionIdentifier,
            $name
        );
    }

    public function canVisit(SortClause $sortClause): bool
    {
        return $sortClause instanceof SortClause\MapLocationDistance;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException If no sortable fields are found for the given sort clause target.
     */
    public function visit(SortClause $sortClause): string
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause\Target\MapLocationTarget $target */
        $target = $sortClause->targetData;
        $fieldName = $this->getSortFieldName(
            $sortClause,
            $target->typeIdentifier,
            $target->fieldIdentifier,
            $this->fieldName
        );

        if ($fieldName === null) {
            throw new InvalidArgumentException(
                '$sortClause->targetData',
                'No searchable Fields found for the provided Sort Clause target ' .
                "'{$target->fieldIdentifier}' on '{$target->typeIdentifier}'."
            );
        }

        return "geodist({$fieldName},{$target->latitude},{$target->longitude})" . $this->getDirection($sortClause);
    }
}
