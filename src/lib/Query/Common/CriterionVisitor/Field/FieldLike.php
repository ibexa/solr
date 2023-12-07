<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\CriterionVisitor\Field;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\Operator;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Solr\Query\Common\CriterionVisitor\Field;

/**
 * Visits the Field criterion.
 */
class FieldLike extends Field
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
        return $criterion instanceof Criterion\Field && $criterion->operator === Operator::LIKE;
    }

    /**
     * Map field value to a proper Solr representation.
     *
     * @throws \Ibexa\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion $criterion
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit(Criterion $criterion, CriterionVisitor $subVisitor = null)
    {
        $searchFields = $this->getSearchFields($criterion);

        if (empty($searchFields)) {
            throw new InvalidArgumentException(
                '$criterion->target',
                "No searchable Fields found for the provided Criterion target '{$criterion->target}'."
            );
        }

        $queries = [];
        foreach ($searchFields as $name => $fieldType) {
            $preparedValue = $this->toString($this->mapSearchFieldValue($criterion->value, $fieldType));

            // Check if there is user supplied wildcard or not
            if (strpos($preparedValue, '*') !== false) {
                $queries[] = $name . ':' . $this->escapeExpressions($preparedValue, true);
            } else {
                $queries[] = $name . ':"' . $this->escapeQuote($preparedValue, true) . '"';
            }
        }

        return '(' . implode(' OR ', $queries) . ')';
    }
}

class_alias(FieldLike::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\CriterionVisitor\Field\FieldLike');
