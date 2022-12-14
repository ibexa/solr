<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query\Common\FacetBuilderVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet;
use Ibexa\Solr\Query\FacetBuilderVisitor;
use Ibexa\Solr\Query\FacetFieldVisitor;

/**
 * Visits the Section facet builder.
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in Ibexa 4.0.0.
 */
class Section extends FacetBuilderVisitor implements FacetFieldVisitor
{
    /**
     * {@inheritdoc}.
     */
    public function mapField($field, array $data, FacetBuilder $facetBuilder)
    {
        return new Facet\SectionFacet(
            [
                'name' => $facetBuilder->name,
                'entries' => $this->mapData($data),
            ]
        );
    }

    /**
     * {@inheritdoc}.
     */
    public function canVisit(FacetBuilder $facetBuilder)
    {
        return $facetBuilder instanceof FacetBuilder\SectionFacetBuilder;
    }

    /**
     * {@inheritdoc}.
     */
    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId)
    {
        return [
            'facet.field' => "{!ex=dt key=$fieldId}content_section_id_id",
            'f.content_section_id_id.facet.limit' => $facetBuilder->limit,
            'f.content_section_id_id.facet.mincount' => $facetBuilder->minCount,
        ];
    }
}

class_alias(Section::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\FacetBuilderVisitor\Section');
