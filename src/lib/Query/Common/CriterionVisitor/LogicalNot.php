<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the LogicalNot criterion.
 */
class LogicalNot extends CriterionVisitor
{
    /**
     * CHeck if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\LogicalNot;
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
        if (!isset($criterion->criteria[0]) ||
             (\count($criterion->criteria) > 1)) {
            throw new \RuntimeException('Invalid aggregation in LogicalNot criterion.');
        }

        return '(*:* NOT ' . $subVisitor->visit($criterion->criteria[0]) . ')';
    }
}

class_alias(LogicalNot::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\LogicalNot');
