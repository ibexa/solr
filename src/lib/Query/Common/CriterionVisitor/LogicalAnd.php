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
 * Visits the LogicalAnd criterion.
 */
class LogicalAnd extends CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\LogicalAnd;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LogicalAnd $criterion */
        if (!isset($criterion->criteria[0])) {
            throw new RuntimeException('Invalid aggregation in LogicalAnd criterion.');
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

        return '(' . implode(' AND ', $subCriteria) . ')';
    }
}

class_alias(LogicalAnd::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\LogicalAnd');
