<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use RuntimeException;

/**
 * Visits the LogicalOr criterion.
 */
class LogicalOr extends CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\LogicalOr;
    }

    public function visit(Criterion $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd $criterion */
        if (!isset($criterion->criteria[0])) {
            throw new RuntimeException('Invalid aggregation in LogicalOr criterion.');
        }

        if (null === $subVisitor) {
            throw new RuntimeException('Sub visitor is required for LogicalOr criterion.');
        }

        $subCriteria = array_map(
            static function ($value) use ($subVisitor) {
                return $subVisitor->visit($value);
            },
            $criterion->criteria
        );

        if (\count($subCriteria) === 1) {
            return reset($subCriteria);
        }

        return '(' . implode(' OR ', $subCriteria) . ')';
    }
}

class_alias(LogicalOr::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\LogicalOr');
