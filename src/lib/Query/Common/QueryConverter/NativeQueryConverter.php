<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Query\Common\QueryConverter;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Solr\Query\AggregationVisitor;
use Ibexa\Contracts\Solr\Query\CriterionVisitor;
use Ibexa\Contracts\Solr\Query\SortClauseVisitor;
use Ibexa\Solr\Query\QueryConverter;

/**
 * Native implementation of Query Converter.
 */
class NativeQueryConverter extends QueryConverter
{
    /**
     * Query visitor.
     *
     * @var \Ibexa\Contracts\Solr\Query\CriterionVisitor
     */
    protected $criterionVisitor;

    /**
     * Sort clause visitor.
     *
     * @var \Ibexa\Contracts\Solr\Query\SortClauseVisitor
     */
    protected $sortClauseVisitor;

    /**
     * @var \Ibexa\Contracts\Solr\Query\AggregationVisitor
     */
    private $aggregationVisitor;

    /**
     * Construct from visitors.
     *
     * @param \Ibexa\Contracts\Solr\Query\CriterionVisitor $criterionVisitor
     * @param \Ibexa\Contracts\Solr\Query\SortClauseVisitor $sortClauseVisitor
     */
    public function __construct(
        CriterionVisitor $criterionVisitor,
        SortClauseVisitor $sortClauseVisitor,
        AggregationVisitor $aggregationVisitor
    ) {
        $this->criterionVisitor = $criterionVisitor;
        $this->sortClauseVisitor = $sortClauseVisitor;
        $this->aggregationVisitor = $aggregationVisitor;
    }

    public function convert(Query $query, array $languageSettings = [])
    {
        $params = [
            'q' => '{!lucene}' . ($query->query !== null ? $this->criterionVisitor->visit($query->query) : ''),
            'fq' => '{!lucene}' . ($query->filter !== null ? $this->criterionVisitor->visit($query->filter) : ''),
            'sort' => $this->getSortClauses($query->sortClauses),
            'start' => $query->offset,
            'rows' => $query->limit,
            'fl' => '*,score,[shard]',
            'wt' => 'json',
        ];

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
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\SortClause[] $sortClauses
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
}
