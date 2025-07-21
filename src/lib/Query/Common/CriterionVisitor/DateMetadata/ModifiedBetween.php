<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor\DateMetadata;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Solr\Query\Common\CriterionVisitor\DateMetadata;

/**
 * Visits the DateMetadata criterion.
 */
class ModifiedBetween extends DateMetadata
{
    /**
     * Check if visitor is applicable to current criterion.
     */
    public function canVisit(CriterionInterface $criterion): bool
    {
        return
            $criterion instanceof Criterion\DateMetadata &&
            $criterion->target === 'modified' &&
            ($criterion->operator === Operator::LT ||
              $criterion->operator === Operator::LTE ||
              $criterion->operator === Operator::GT ||
              $criterion->operator === Operator::GTE ||
              $criterion->operator === Operator::BETWEEN);
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata $criterion
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        $start = $this->getSolrTime($criterion->value[0]);
        $end = isset($criterion->value[1]) ? $this->getSolrTime($criterion->value[1]) : null;

        if (($criterion->operator === Operator::LT) ||
             ($criterion->operator === Operator::LTE)) {
            $end = $start;
            $start = null;
        }

        return 'content_modification_date_dt:' . $this->getRange($criterion->operator, $start, $end);
    }
}
