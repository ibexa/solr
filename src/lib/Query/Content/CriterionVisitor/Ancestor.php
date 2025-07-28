<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Content\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Ancestor as AncestorCriterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the Ancestor criterion.
 */
class Ancestor extends CriterionVisitor
{
    public function canVisit(CriterionInterface $criterion): bool
    {
        return $criterion instanceof AncestorCriterion;
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Ancestor $criterion
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
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
                    static fn (string $value): string => 'location_id_mid:"' . $value . '"',
                    array_keys($idSet)
                )
            ) .
            ')';
    }
}
