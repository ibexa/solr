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
class ModifiedIn extends DateMetadata
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(CriterionInterface $criterion)
    {
        return
            $criterion instanceof Criterion\DateMetadata &&
            $criterion->target === 'modified' &&
            (
                ($criterion->operator ?: Operator::IN) === Operator::IN ||
                $criterion->operator === Operator::EQ
            );
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata $criterion
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(CriterionInterface $criterion, CriterionVisitor $subVisitor = null)
    {
        $values = [];
        foreach ($criterion->value as $value) {
            $values[] = 'content_modification_date_dt:"' . $this->getSolrTime($value) . '"';
        }

        return '(' . implode(' OR ', $values) . ')';
    }
}
