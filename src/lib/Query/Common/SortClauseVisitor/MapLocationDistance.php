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
    /**
     * Name of the field type's indexed field that criterion can handle.
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Field name resolver.
     *
     * @var \Ibexa\Core\Search\Common\FieldNameResolver
     */
    protected $fieldNameResolver;

    /**
     * Create from field name resolver and field name.
     *
     * @param \Ibexa\Core\Search\Common\FieldNameResolver $fieldNameResolver
     * @param string $fieldName
     */
    public function __construct(FieldNameResolver $fieldNameResolver, $fieldName)
    {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->fieldName = $fieldName;
    }

    /**
     * Get sort field name.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause $sortClause
     * @param string $contentTypeIdentifier
     * @param string $fieldDefinitionIdentifier
     * @param string $name
     *
     * @return array
     */
    protected function getSortFieldName(
        SortClause $sortClause,
        $contentTypeIdentifier,
        $fieldDefinitionIdentifier,
        $name = null
    ) {
        return $this->fieldNameResolver->getSortFieldName(
            $sortClause,
            $contentTypeIdentifier,
            $fieldDefinitionIdentifier,
            $name
        );
    }

    /**
     * Check if visitor is applicable to current sortClause.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return bool
     */
    public function canVisit(SortClause $sortClause)
    {
        return $sortClause instanceof SortClause\MapLocationDistance;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException If no sortable fields are found for the given sort clause target.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause $sortClause
     *
     * @return string
     */
    public function visit(SortClause $sortClause)
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
