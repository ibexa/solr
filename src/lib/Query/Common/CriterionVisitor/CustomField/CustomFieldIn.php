<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor\CustomField;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the CustomField criterion with IN, EQ or CONTAINS operator.
 */
class CustomFieldIn extends CriterionVisitor
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(CriterionInterface $criterion): bool
    {
        return
            $criterion instanceof Criterion\CustomField &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ ||
                $criterion->operator === Operator::CONTAINS
            );
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField $criterion
     *
     * @return string
     */
    public function visit(CriterionInterface $criterion, CriterionVisitor $subVisitor = null): string
    {
        $queries = [];
        $values = (array)$criterion->value;

        foreach ($values as $value) {
            $preparedValue = $this->escapeQuote($this->toString($value), true);

            if ($this->isRegExp($preparedValue)) {
                $queries[] = $criterion->target . ':' . $preparedValue;
            } else {
                $queries[] = $criterion->target . ':"' . $preparedValue . '"';
            }
        }

        return '(' . implode(' OR ', $queries) . ')';
    }

    private function isRegExp($preparedValue): int|false
    {
        return preg_match('#^/.*/$#', $preparedValue);
    }
}
