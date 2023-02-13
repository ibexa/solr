<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder;

/**
 * Visits Solr results into correct facet and facet builder combination.
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in Ibexa 4.0.0.
 */
interface FacetFieldVisitor
{
    /**
     * Map Solr facet result back to facet objects.
     *
     * @param string $field
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet
     */
    public function mapField($field, array $data, FacetBuilder $facetBuilder);

    /**
     * Map field value to a proper Solr representation.
     *
     * Example:
     *        return array(
     *            'facet.field' => "{!ex=dt key=$fieldId}content_type_id_id",
     *            'f.content_type_id_id.facet.limit' => $facetBuilder->limit,
     *            'f.content_type_id_id.facet.mincount' => $facetBuilder->minCount,
     *        );
     *
     * @param string $fieldId Id to identify the field in Solr facet.
     *
     * @return string[]
     */
    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId);
}

class_alias(FacetFieldVisitor::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\FacetFieldVisitor');
