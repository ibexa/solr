<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\QueryConverter;

use Ibexa\Contracts\Core\Repository\Values\Content\EmbeddingQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\FacetBuilder;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Contracts\Solr\Query\EmbeddingVisitor;
use Ibexa\Contracts\Solr\Query\SortClauseVisitor;
use Ibexa\Solr\Query\FacetFieldVisitor;
use Ibexa\Solr\Query\QueryConverter;

/**
 * Native implementation of Query Converter.
 */
class NativeQueryConverter extends QueryConverter
{
    /**
     * Query visitor.
     *
     * @var CriterionVisitor
     */
    protected $criterionVisitor;

    /**
     * Sort clause visitor.
     *
     * @var SortClauseVisitor
     */
    protected $sortClauseVisitor;

    /**
     * Facet builder visitor.
     *
     * @var FacetFieldVisitor
     */
    protected $facetBuilderVisitor;

    /**
     * @var AggregationVisitor
     */
    private $aggregationVisitor;

    private EmbeddingVisitor $embeddingVisitor;

    /**
     * Construct from visitors.
     *
     * @param CriterionVisitor $criterionVisitor
     * @param SortClauseVisitor $sortClauseVisitor
     * @param FacetFieldVisitor $facetBuilderVisitor
     */
    public function __construct(
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor,
        FacetFieldVisitor $facetBuilderVisitor,
        AggregationVisitor $aggregationVisitor,
        EmbeddingVisitor $embeddingVisitor
    ) {
        $this->criterionVisitor = $criterionVisitor;
        $this->sortClauseVisitor = $sortClauseVisitor;
        $this->facetBuilderVisitor = $facetBuilderVisitor;
        $this->aggregationVisitor = $aggregationVisitor;
        $this->embeddingVisitor = $embeddingVisitor;
    }

    public function convert(
        Query $query,
        array $languageSettings = []
    ) {
        $params = [
            'q' => '{!lucene}' . $this->criterionVisitor->visit($query->query),
            'fq' => ['{!lucene}' . $this->criterionVisitor->visit($query->filter)],
            'sort' => $this->getSortClauses($query->sortClauses),
            'start' => $query->offset,
            'rows' => $query->limit,
            'fl' => '*,score,[shard]',
            'wt' => 'json',
        ];

        if ($query instanceof EmbeddingQuery && $query->getEmbedding() !== null) {
            $params['fq'][] = $this->embeddingVisitor->visit($query->getEmbedding(), $query->limit);
        }

        $facetParams = $this->getFacetParams($query->facetBuilders);
        if (!empty($facetParams)) {
            $params['facet'] = 'true';
            $params['facet.sort'] = 'count';
            $params = array_merge_recursive($facetParams, $params);
        }

        if (!empty($query->aggregations)) {
            $aggregations = [];

            foreach ($query->aggregations as $aggregation) {
                if ($this->aggregationVisitor->canVisit($aggregation, $languageSettings)) {
                    $aggregations[$aggregation->getName()] = $this->aggregationVisitor->visit(
                        $this->aggregationVisitor,
                        $aggregation,
                        $languageSettings
                    );
                }
            }

            if (!empty($aggregations)) {
                $params['json.facet'] = json_encode($aggregations);
            }
        }

        if ($query->spellcheck !== null) {
            $params['spellcheck'] = 'true';
            $params['spellcheck.q'] = $query->spellcheck->getQuery();
            $params['spellcheck.count'] = 1;
            $params['spellcheck.collate'] = 'true';
        }

        return $params;
    }

    /**
     * Converts an array of sort clause objects to a proper Solr representation.
     *
     * @param SortClause[] $sortClauses
     *
     * @return string
     */
    private function getSortClauses(array $sortClauses)
    {
        return implode(
            ', ',
            array_map(
                [$this->sortClauseVisitor, 'visit'],
                $sortClauses
            )
        );
    }

    /**
     * Converts an array of facet builder objects to a Solr query parameters representation.
     *
     * This method uses spl_object_hash() to get id of each and every facet builder, as this
     * is expected by {@link \Ibexa\Solr\ResultExtractor}.
     *
     * @param FacetBuilder[] $facetBuilders
     *
     * @return array
     */
    private function getFacetParams(array $facetBuilders)
    {
        $facetSets = array_map(
            function ($facetBuilder) {
                return $this->facetBuilderVisitor->visitBuilder($facetBuilder, spl_object_hash($facetBuilder));
            },
            $facetBuilders
        );

        $facetParams = [];

        // In case when facet sets contain same keys, merge them in an array
        foreach ($facetSets as $facetSet) {
            foreach ($facetSet as $key => $value) {
                if (isset($facetParams[$key])) {
                    if (!is_array($facetParams[$key])) {
                        $facetParams[$key] = [$facetParams[$key]];
                    }
                    $facetParams[$key][] = $value;
                } else {
                    $facetParams[$key] = $value;
                }
            }
        }

        return $facetParams;
    }
}

class_alias(NativeQueryConverter::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\Common\QueryConverter\NativeQueryConverter');
