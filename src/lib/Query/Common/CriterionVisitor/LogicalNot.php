<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use RuntimeException;

/**
 * Visits the LogicalNot criterion.
 */
class LogicalNot extends CriterionVisitor
{
    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\LogicalNot;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalNot $criterion
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        if (!isset($criterion->criteria[0]) ||
             (\count($criterion->criteria) > 1)) {
            throw new RuntimeException('Invalid aggregation in LogicalNot criterion.');
        }

        if (null === $subVisitor) {
            throw new RuntimeException('Sub visitor is required for LogicalNot criterion.');
        }

        return '(*:* NOT ' . $subVisitor->visit($criterion->criteria[0]) . ')';
    }
}
