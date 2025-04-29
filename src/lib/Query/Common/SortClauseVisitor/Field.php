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
    /**
     * Field name resolver.
     */
    protected FieldNameResolver $fieldNameResolver;

    /**
     * Create from field name resolver.
     *
     * @param \Ibexa\Core\Search\Common\FieldNameResolver $fieldNameResolver
     */
    public function __construct(FieldNameResolver $fieldNameResolver)
    {
        $this->fieldNameResolver = $fieldNameResolver;
    }

    /**
     * Get sort field name.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause $sortClause
     * @param string $contentTypeIdentifier
     * @param string $fieldDefinitionIdentifier
     *
     * @return array
     */
    protected function getSortFieldName(
        SortClause $sortClause,
        $contentTypeIdentifier,
        $fieldDefinitionIdentifier
    ) {
        return $this->fieldNameResolver->getSortFieldName(
            $sortClause,
            $contentTypeIdentifier,
            $fieldDefinitionIdentifier
        );
    }

    /**
     * Check if visitor is applicable to the $sortClause.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\Field;
    }

    /**
     * Map the $sortClause to a proper Solr representation.
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException If no sortable fields are
     *         found for the given sort clause target.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return string
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
