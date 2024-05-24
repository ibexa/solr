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
 * Visits the Field criterion.
 */
class FieldRange extends Field
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
            $criterion instanceof Criterion\Field &&
            ($criterion->operator === Operator::LT ||
              $criterion->operator === Operator::LTE ||
              $criterion->operator === Operator::GT ||
              $criterion->operator === Operator::GTE ||
              $criterion->operator === Operator::BETWEEN);
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
        $searchFields = $this->getSearchFields($criterion);

        if (empty($searchFields)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable Fields found for the provided Criterion target '{$criterion->target}'."
            );
        }

        $value = (array)$criterion->value;
        $queries = [];
        foreach ($searchFields as $name => $fieldType) {
            $start = $this->mapSearchFieldValue($value[0], $fieldType);
            $end = isset($value[1]) ? $this->mapSearchFieldvalue($value[1], $fieldType) : null;

            if (($criterion->operator === Operator::LT) ||
                  ($criterion->operator === Operator::LTE)) {
                $end = $start;
                $start = null;
            }

            $queries[] = $name . ':' . $this->getRange($criterion->operator, $start, $end);
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}

class_alias(FieldRange::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field\FieldRange');
