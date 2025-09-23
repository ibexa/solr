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
 * Visits the CustomField criterion with LT, LTE, GT, GTE or BETWEEN operator.
 */
class CustomFieldRange extends CriterionVisitor
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
                $criterion->operator === Operator::LT ||
                $criterion->operator === Operator::LTE ||
                $criterion->operator === Operator::GT ||
                $criterion->operator === Operator::GTE ||
                $criterion->operator === Operator::BETWEEN
            );
    }

    public function visit(Criterion $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        $values = (array)$criterion->value;
        $start = $values[0];
        $end = isset($values[1]) ? $values[1] : null;

        if (($criterion->operator === Operator::LT) || ($criterion->operator === Operator::LTE)) {
            $end = $start;
            $start = null;
        }

        return $criterion->target . ':' . $this->getRange($criterion->operator, $start, $end);
    }
}

class_alias(CustomFieldRange::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\CustomField\CustomFieldRange');
