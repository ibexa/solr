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
    /**
     * Field map.
     *
     * @var \Ibexa\Core\Search\Common\FieldNameResolver
     */
    protected $fieldNameResolver;

    /**
     * @var \Ibexa\Core\Search\Common\FieldValueMapper
     */
    protected $fieldValueMapper;

    public function __construct(FieldNameResolver $fieldNameResolver, FieldValueMapper $fieldValueMapper)
    {
        $this->fieldNameResolver = $fieldNameResolver;
        $this->fieldValueMapper = $fieldValueMapper;
    }

    /**
     * Get array of search fields.
     *
     * @return \Ibexa\Contracts\Core\Search\FieldType[] Array of field types indexed by name.
     */
    protected function getSearchFields(Criterion $criterion)
    {
        return $this->fieldNameResolver->getFieldTypes(
            $criterion,
            $criterion->target
        );
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function mapSearchFieldValue($value, ?FieldType $searchFieldType = null)
    {
        if (null === $searchFieldType) {
            return $value;
        }

        $searchField = new SearchField('field', $value, $searchFieldType);
        $value = (array)$this->fieldValueMapper->map($searchField);

        return current($value);
    }
}

class_alias(Field::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field');
