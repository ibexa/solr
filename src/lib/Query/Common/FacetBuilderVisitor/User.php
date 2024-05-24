<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\FacetBuilderVisitor;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\UserFacetBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\Facet;
use Ibexa\Solr\Query\FacetBuilderVisitor;
use Ibexa\Solr\Query\FacetFieldVisitor;

/**
 * Visits the User facet builder.
 *
 * @deprecated since eZ Platform 3.2.0, to be removed in Ibexa 4.0.0.
 */
class User extends FacetBuilderVisitor implements FacetFieldVisitor
{
    /**
     * @internal Will be marked private when we require PHP 7.0 and can do that.
     */
    public const DOC_FIELD_MAP = [
        UserFacetBuilder::OWNER => 'content_owner_user_id_id',
        UserFacetBuilder::GROUP => 'content_owner_user_group_ids_mid',
        UserFacetBuilder::MODIFIER => 'content_version_creator_user_id_id',
    ];

    /**
     * {@inheritdoc}.
     */
    public function mapField($field, array $data, FacetBuilder $facetBuilder)
    {
        return new Facet\UserFacet(
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
        return $facetBuilder instanceof UserFacetBuilder;
    }

    /**
     * {@inheritdoc}.
     */
    public function visitBuilder(FacetBuilder $facetBuilder, $fieldId)
    {
        /** @var \Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder\UserFacetBuilder $facetBuilder */
        $field = self::DOC_FIELD_MAP[$facetBuilder->type];

        return [
            'facet.field' => "{!ex=dt key=$fieldId}$field",
            "f.$field.facet.limit" => $facetBuilder->limit,
            "f.$field.facet.mincount" => $facetBuilder->minCount,
        ];
    }
}

class_alias(User::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\FacetBuilderVisitor\User');
