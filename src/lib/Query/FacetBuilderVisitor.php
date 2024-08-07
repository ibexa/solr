<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder;

/**
 * Visits the facet builder tree into a Solr query.
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in Ibexa 4.0.0.
 */
abstract class FacetBuilderVisitor
{
    /**
     * Check if visitor is applicable to current facet result.
     *
     * @deprecated Not needed anymore if visit() correctly used $id param to identify facetBuilder.
     *
     * @param string $field
     *
     * @return bool
     */
    public function canMap($field)
    {
        throw new \LogicException('Deprecated in favour of FacetFieldVisitor, not in use if FacetFieldVisitor is implemented');
    }

    /**
     * Map Solr facet result back to facet objects.
     *
     * @deprecated Will be removed in 2.0, replaced by {@link FacetFieldVisitor::mapField()}.
     *
     * @param string $field
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet
     */
    public function map($field, array $data)
    {
        throw new \LogicException('Deprecated in favour of FacetFieldVisitor, not in use if FacetFieldVisitor is implemented');
    }

    /**
     * Check if visitor is applicable to current facet builder.
     *
     * @return bool
     */
    abstract public function canVisit(FacetBuilder $facetBuilder);

    /**
     * Map field value to a proper Solr representation.
     *
     * @deprecated Will be removed in 2.0, replaced by {@link FacetFieldVisitor::visitBuilder()}.
     *
     * @return string[]
     */
    public function visit(FacetBuilder $facetBuilder)
    {
        throw new \LogicException('Deprecated in favour of FacetFieldVisitor, not in use if FacetFieldVisitor is implemented');
    }

    /**
     * Map Solr return array into a sane hash map.
     *
     * @return array
     */
    protected function mapData(array $data)
    {
        $values = [];
        reset($data);
        while ($key = current($data)) {
            $values[$key] = next($data);
            next($data);
        }

        return $values;
    }
}
