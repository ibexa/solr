<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Location\CriterionVisitor\Location;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the Depth criterion.
 */
class DepthBetween extends CriterionVisitor
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(CriterionInterface $criterion)
    {
        return
            $criterion instanceof Criterion\Location\Depth &&
            (
                $criterion->operator === Operator::LT ||
                $criterion->operator === Operator::LTE ||
                $criterion->operator === Operator::GT ||
                $criterion->operator === Operator::GTE ||
                $criterion->operator === Operator::BETWEEN
            );
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Location\Depth $criterion
     *
     * @return string
     */
    public function visit(CriterionInterface $criterion, CriterionVisitor $subVisitor = null)
    {
        $start = $criterion->value[0];
        $end = isset($criterion->value[1]) ? $criterion->value[1] : null;

        if (
            ($criterion->operator === Operator::LT) ||
            ($criterion->operator === Operator::LTE)
        ) {
            $end = $start;
            $start = null;
        }

        return 'depth_i:' . $this->getRange($criterion->operator, $start, $end);
    }
}
