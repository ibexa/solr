<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\CriterionVisitor\Field;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Solr\Query\Common\CriterionVisitor\Field;

/**
 * Visits the FieldRelation criterion.
 */
class FieldRelation extends Field
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\FieldRelation &&
            (($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::CONTAINS);
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion $criterion
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $searchFields = $this->getSearchFields($criterion, $criterion->target);

        if (empty($searchFields)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable Fields found for the provided Criterion target '{$criterion->target}'."
            );
        }

        $criterion->value = (array)$criterion->value;

        $queries = [];
        foreach ($searchFields as $name => $fieldType) {
            foreach ($criterion->value as $value) {
                $preparedValues = (array)$this->mapSearchFieldvalue($value, $fieldType);
                foreach ($preparedValues as $prepValue) {
                    $queries[] = $name . ':"' . $this->escapeQuote($this->toString($prepValue), true) . '"';
                }
            }
        }

        switch ($criterion->operator) {
            case Operator::CONTAINS:
                $op = ' AND ';
                break;
            case Operator::IN:
            default:
                $op = ' OR ';
        }

        return '(' . implode($op, $queries) . ')';
    }
}

class_alias(FieldRelation::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field\FieldRelation');
