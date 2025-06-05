<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\CriterionVisitor\MapLocation;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Solr\Query\Common\CriterionVisitor\MapLocation;

/**
 * Visits the MapLocationDistance criterion.
 */
class MapLocationDistanceRange extends MapLocation
{
    /**
     * Check if visitor is applicable to current criterion.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return
            $criterion instanceof Criterion\MapLocationDistance &&
            ($criterion->operator === Operator::LT ||
                $criterion->operator === Operator::LTE ||
                $criterion->operator === Operator::GT ||
                $criterion->operator === Operator::GTE ||
                $criterion->operator === Operator::BETWEEN);
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion $criterion
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        if (is_array($criterion->value)) {
            $minDistance = $criterion->value[0];
            $maxDistance = $criterion->value[1] ?? 63510;
        } else {
            $minDistance = 0;
            $maxDistance = $criterion->value;
        }

        $sign = '';
        if (($criterion->operator === Operator::GT) ||
            ($criterion->operator === Operator::GTE)) {
            $sign = '-';
        }

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

        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Value\MapLocationValue $location */
        $location = $criterion->valueData;

        $queries = [];
        foreach ($searchFields as $name => $fieldType) {
            if ($criterion->operator === Operator::BETWEEN) {
                $query = sprintf(
                    '{!geofilt sfield=%s pt=%F,%F d=%s} AND -{!geofilt sfield=%s pt=%F,%F d=%s}',
                    $name,
                    $location->latitude,
                    $location->longitude,
                    $maxDistance,
                    $name,
                    $location->latitude,
                    $location->longitude,
                    $minDistance
                );
            } else {
                $query = sprintf('%s{!geofilt sfield=%s pt=%F,%F d=%s}', $sign, $name, $location->latitude, $location->longitude, $maxDistance);
            }

            $queries[] = "{$query} AND {$name}:[* TO *]";
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}
