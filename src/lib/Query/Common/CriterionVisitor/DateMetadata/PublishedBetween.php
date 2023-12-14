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
class PublishedBetween extends DateMetadata
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        if (!$criterion instanceof Criterion\DateMetadata) {
            return false;
        }

        if (!in_array($criterion->target, [Criterion\DateMetadata::PUBLISHED, Criterion\DateMetadata::CREATED])) {
            return false;
        }

        return in_array($criterion->operator, [
            Operator::LT,
            Operator::LTE,
            Operator::GT,
            Operator::GTE,
            Operator::BETWEEN,
        ], true);
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
        $start = $this->getSolrTime($criterion->value[0]);
        $end = isset($criterion->value[1]) ? $this->getSolrTime($criterion->value[1]) : null;

        if (($criterion->operator === Operator::LT) ||
             ($criterion->operator === Operator::LTE)) {
            $end = $start;
            $start = null;
        }

        return 'content_publication_date_dt:' . $this->getRange($criterion->operator, $start, $end);
    }
}

class_alias(PublishedBetween::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\DateMetadata\PublishedBetween');
