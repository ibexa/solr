<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\CriterionVisitor\MapLocation;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\CriterionInterface;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Solr\Query\Common\CriterionVisitor\MapLocation;

/**
 * Visits the MapLocationDistance criterion.
 */
class MapLocationDistanceIn extends MapLocation
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion $criterion
     */
    public function canVisit(CriterionInterface $criterion): bool
    {
        return
            $criterion instanceof Criterion\MapLocationDistance &&
            (($criterion->operator ?: Operator::IN) === Operator::IN ||
              $criterion->operator === Operator::EQ);
    }

    /**
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\MapLocationDistance $criterion
     */
    public function visit(CriterionInterface $criterion, ?CriterionVisitor $subVisitor = null): string
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location */
        $location = $criterion->valueData;
        $criterion->value = (array)$criterion->value;

        $searchFields = $this->getSearchFields(
            $criterion,
            $criterion->target,
            $this->fieldTypeIdentifier,
            $this->fieldName
        );

        if (empty($searchFields)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable Fields found for the provided Criterion target '{$criterion->target}'."
            );
        }

        $queries = [];
        foreach ($criterion->value as $value) {
            foreach ($searchFields as $name => $fieldType) {
                $queries[] = sprintf('geodist(%s,%F,%F):%s', $name, $location->latitude, $location->longitude, $value);
            }
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}
