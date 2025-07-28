<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Content\CriterionVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;

/**
 * Visits the LocationRemoteId criterion.
 */
class LocationRemoteIdIn extends CriterionVisitor
{
    public function canVisit(CriterionInterface $criterion): bool
    {
        return
            $criterion instanceof Criterion\LocationRemoteId &&
            (($criterion->operator ?: Operator::IN) === Operator::IN ||
              $criterion->operator === Operator::EQ);
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\LocationRemoteId $criterion
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        return '(' .
            implode(
                ' OR ',
                array_map(
                    static fn (bool|float|int|string $id): string => 'location_remote_id_mid:"' . $id . '"',
                    $criterion->value
                )
            ) .
            ')';
    }
}
