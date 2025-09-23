<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\CriterionVisitor\CustomField;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
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
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\CustomField &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ ||
                $criterion->operator === Operator::CONTAINS
            );
    }

    public function visit(Criterion $criterion, ?CriterionVisitor $subVisitor = null): string
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

    private function isRegExp($preparedValue)
    {
        return preg_match('#^/.*/$#', $preparedValue);
    }
}

class_alias(CustomFieldIn::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\CustomField\CustomFieldIn');
