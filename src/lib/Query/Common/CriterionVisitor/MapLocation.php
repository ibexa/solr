<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Core\Search\Common\FieldNameResolver;

/**
 * Visits the MapLocation criterion.
 */
abstract class MapLocation extends CriterionVisitor
{
    /**
     * Field map.
     *
     * @var \Ibexa\Core\Search\Common\FieldNameResolver
     */
    protected $fieldNameResolver;

    /**
     * Identifier of the field type that criterion can handle.
     *
     * @var string
     */
    protected $fieldTypeIdentifier;

    /**
     * Name of the field type's indexed field that criterion can handle.
     *
     * @var string
     */
    protected $fieldName;

    /**
     * Create from FieldNameResolver, FieldType identifier and field name.
     *
     * @param string $fieldTypeIdentifier
     * @param string $fieldName
     */
    public function __construct(FieldNameResolver $fieldNameResolver, $fieldTypeIdentifier, $fieldName)
    {
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
        $this->fieldName = $fieldName;

        $this->fieldNameResolver = $fieldNameResolver;
    }

    /**
     * Get array of search fields.
     *
     * @param string $fieldDefinitionIdentifier
     * @param string $fieldTypeIdentifier
     * @param string $name
     *
     * @return array
     */
    protected function getSearchFields(
        CriterionInterface $criterion,
        $fieldDefinitionIdentifier,
        $fieldTypeIdentifier = null,
        $name = null
    ) {
        return $this->fieldNameResolver->getFieldTypes(
            $criterion,
            $fieldDefinitionIdentifier,
            $fieldTypeIdentifier,
            $name
        );
    }
}
