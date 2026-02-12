<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the LanguageCode criterion.
 */
class LanguageCodeIn extends CriterionVisitor
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\LanguageCode &&
            (($criterion->operator ?: Operator::IN) === Operator::IN ||
              $criterion->operator === Operator::EQ);
    }

    public function visit(Criterion $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        /** @var Criterion\LanguageCode $criterion */
        $values = is_array($criterion->value) ? $criterion->value : [$criterion->value];
        // content_language_codes_ms is a string field which uses LowerCaseFilter.
        // Since {!terms} bypasses analysis, we must manually lowercase the values to match the index.
        /** @param bool|float|int|string $value */
        $values = array_map(static function ($value): string {
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

class_alias(LanguageCodeIn::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\LanguageCodeIn');
