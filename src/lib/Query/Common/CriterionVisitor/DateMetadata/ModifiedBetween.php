<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\CriterionVisitor\DateMetadata;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Solr\Query\Common\CriterionVisitor\DateMetadata;

/**
 * Visits the DateMetadata criterion.
 */
class ModifiedBetween extends DateMetadata
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
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

    public function visit(Criterion $criterion, ?CriterionVisitor $subVisitor = null): string
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

class_alias(ModifiedBetween::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\DateMetadata\ModifiedBetween');
