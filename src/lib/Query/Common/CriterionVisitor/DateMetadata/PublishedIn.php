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
class PublishedIn extends DateMetadata
{
    /**
     * Check if visitor is applicable to current criterion.
     */
    public function canVisit(CriterionInterface $criterion): bool
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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\DateMetadata $criterion
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        return implode(
            ' OR ',
            array_map(
                fn ($value): string => 'content_publication_date_dt:"' . $this->getSolrTime($value) . '"',
                $criterion->value
            )
        );
    }
}
