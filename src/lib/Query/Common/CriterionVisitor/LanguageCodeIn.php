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

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $languageCodeExpressions = array_map(
            static function ($value) {
                return 'content_language_codes_ms:"' . $value . '"';
            },
            $criterion->value
        );

        /** @var Criterion\LanguageCode $criterion */
        if ($criterion->matchAlwaysAvailable) {
            $languageCodeExpressions[] = 'content_always_available_b:true';
        }

        return '(' . implode(' OR ', $languageCodeExpressions) . ')';
    }
}
