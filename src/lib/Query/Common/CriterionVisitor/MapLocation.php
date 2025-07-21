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
     * Create from FieldNameResolver, FieldType identifier and field name.
     *
     * @param string $fieldTypeIdentifier Identifier of the field type that criterion can handle.
     * @param string $fieldName Name of the field type's indexed field that criterion can handle.
     */
    public function __construct(
        protected FieldNameResolver $fieldNameResolver,
        protected string $fieldTypeIdentifier,
        protected string $fieldName
    ) {
    }

    /**
     * Get array of search fields.
     *
     * @return array<string, \Ibexa\Contracts\Core\Search\FieldType>
     */
    protected function getSearchFields(
        CriterionInterface $criterion,
        string $fieldDefinitionIdentifier,
        ?string $fieldTypeIdentifier = null,
        ?string $name = null
    ): array {
        return $this->fieldNameResolver->getFieldTypes(
            $criterion,
            $fieldDefinitionIdentifier,
            $fieldTypeIdentifier,
            $name
        );
    }
}
