<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Content\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Ancestor as AncestorCriterion;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the Ancestor criterion.
 */
class Ancestor extends CriterionVisitor
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof AncestorCriterion;
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
        $idSet = [];
        foreach ($criterion->value as $value) {
            foreach (explode('/', trim((string)$value, '/')) as $id) {
                $idSet[$id] = true;
            }
        }

        return '(' .
            implode(
                ' OR ',
                array_map(
                    static function ($value) {
                        return 'location_id_mid:"' . $value . '"';
                    },
                    array_keys($idSet)
                )
            ) .
            ')';
    }
}
