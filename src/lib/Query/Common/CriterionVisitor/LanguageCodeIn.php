<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the LanguageCode criterion.
 */
class LanguageCodeIn extends CriterionVisitor
{
    /**
     * Check if visitor is applicable to current criterion.
     */
    public function canVisit(CriterionInterface $criterion): bool
    {
        return
            $criterion instanceof Criterion\LanguageCode &&
            (($criterion->operator ?: Operator::IN) === Operator::IN ||
              $criterion->operator === Operator::EQ);
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LanguageCode $criterion
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        /** @var Criterion\LanguageCode $criterion */
        $values = is_array($criterion->value) ? $criterion->value : [$criterion->value];
        // content_language_codes_ms is a string field which uses LowerCaseFilter.
        // Since {!terms} bypasses analysis, we must manually lowercase the values to match the index.
        $values = array_map(static function (bool|float|int|string $value): string {
            return strtolower((string)$value);
        }, $values);

        $termsQuery = sprintf(
            '_query_:"{!terms f=content_language_codes_ms}%s"',
            implode(',', $values)
        );

        if ($criterion->matchAlwaysAvailable) {
            return '(' . $termsQuery . ' OR content_always_available_b:true)';
        }

        return $termsQuery;
    }
}
