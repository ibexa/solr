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
class PublishedIn extends DateMetadata
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

        $operator = $criterion->operator ?: Operator::IN;

        return in_array($operator, [Operator::IN, Operator::EQ], true);
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
        return implode(
            ' OR ',
            array_map(
                function ($value) {
                    return 'content_publication_date_dt:"' . $this->getSolrTime($value) . '"';
                },
                $criterion->value
            )
        );
    }
}

class_alias(PublishedIn::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\DateMetadata\PublishedIn');
