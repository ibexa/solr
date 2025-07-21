<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Search\Field as SearchField;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Core\Search\Common\FieldNameResolver;
use Ibexa\Core\Search\Common\FieldValueMapper;

/**
 * Base class for Field criterion visitors.
 *
 * @api
 */
abstract class Field extends CriterionVisitor
{
    public function __construct(
        protected readonly FieldNameResolver $fieldNameResolver,
        protected readonly FieldValueMapper $fieldValueMapper
    ) {
    }

    /**
     * Get array of search fields.
     *
     * @return \Ibexa\Contracts\Core\Search\FieldType[] Array of field types indexed by name.
     */
    protected function getSearchFields(Criterion $criterion): array
    {
        return $this->fieldNameResolver->getFieldTypes(
            $criterion,
            $criterion->target
        );
    }

    /**
     * Map search field value to solr value using FieldValueMapper.
     */
    protected function mapSearchFieldValue(mixed $value, ?FieldType $searchFieldType = null): mixed
    {
        if (null === $searchFieldType) {
            return $value;
        }

        $searchField = new SearchField('field', $value, $searchFieldType);
        $value = (array)$this->fieldValueMapper->map($searchField);

        return current($value);
    }
}
