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
 * Visits the LogicalOr criterion.
 */
class LogicalOr extends CriterionVisitor
{
    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof Criterion\LogicalOr;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalOr $criterion
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd $criterion */
        if (!isset($criterion->criteria[0])) {
            throw new RuntimeException('Invalid aggregation in LogicalOr criterion.');
        }

        if (null === $subVisitor) {
            throw new RuntimeException('Sub visitor is required for LogicalOr criterion.');
        }

        $subCriteria = array_map(
            static fn (CriterionInterface $value): string => $subVisitor->visit($value),
            $criterion->criteria
        );

        if (\count($subCriteria) === 1) {
            return $subCriteria[array_key_first($subCriteria)];
        }

        return '(' . implode(' OR ', $subCriteria) . ')';
    }
}
