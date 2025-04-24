<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr;

use Ibexa\Contracts\Core\Repository\Values\Content\Query\Spellcheck;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\AggregationResultCollection;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchHit;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SpellcheckResult;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use Ibexa\Solr\Gateway\EndpointRegistry;
use stdClass;

/**
 * Abstract implementation of Search Extractor, which extracts search result
 * from the data returned by Solr backend.
 */
abstract class ResultExtractor
{
    protected AggregationResultExtractor $aggregationResultExtractor;

    protected EndpointRegistry $endpointRegistry;

    public function __construct(
        AggregationResultExtractor $aggregationResultExtractor,
        EndpointRegistry $endpointRegistry
    ) {
        $this->aggregationResultExtractor = $aggregationResultExtractor;
        $this->endpointRegistry = $endpointRegistry;
    }

    /**
     * Extracts search result from $data returned by Solr backend.
     *
     * @param \stdClass $data
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation[] $aggregations
     * @param array $languageFilter
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult<\Ibexa\Contracts\Core\Repository\Values\ValueObject>
     */
    public function extract(
        stdClass $data,
        array $aggregations = [],
        array $languageFilter = [],
        ?Spellcheck $spellcheck = null
    ) {
        $result = new SearchResult(
            [
                'time' => $data->responseHeader->QTime / 1000,
                'maxScore' => $data->response->maxScore,
                'totalCount' => $data->response->numFound,
            ]
        );

        $result->aggregations = $this->extractAggregations($data, $aggregations, $languageFilter);
        $result->spellcheck = $this->extractSpellcheck($data, $spellcheck);

        foreach ($data->response->docs as $doc) {
            $result->searchHits[] = $this->extractSearchHit($doc, $languageFilter);
        }

        return $result;
    }

    /**
     * Extracts value object from $hit returned by Solr backend.
     *
     * Needs to be implemented by the concrete ResultExtractor.
     *
     * @param mixed $hit
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\ValueObject
     */
    abstract public function extractHit($hit);

    /**
     * Returns language code of the Content's translation of the matched document.
     *
     * @param mixed $hit
     *
     * @return string
     */
    protected function getMatchedLanguageCode($hit)
    {
        return $hit->meta_indexed_language_code_s;
    }

    /**
     * Returns the identifier of the logical index (shard) of the matched document.
     *
     * @param mixed $hit
     *
     * @return string
     */
    protected function getIndexIdentifier($hit)
    {
        // In single core setup, shard parameter is not set on request to avoid issues in environments that does not
        // know about own dns, which means it's not set here either
        if ($hit->{'[shard]'} === '[not a shard request]') {
            return $this->endpointRegistry->getFirstEndpoint()->getIdentifier();
        }

        return $hit->{'[shard]'};
    }

    /**
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation[] $aggregations
     */
    protected function extractAggregations(
        stdClass $data,
        array $aggregations,
        array $languageFilter
    ): AggregationResultCollection {
        $aggregationsResults = [];
        foreach ($aggregations as $aggregation) {
            $name = $aggregation->getName();

            if (isset($data->facets->{$name})) {
                $aggregationsResults[] = $this->aggregationResultExtractor->extract(
                    $aggregation,
                    $languageFilter,
                    $data->facets->{$name}
                );
            }
        }

        return new AggregationResultCollection($aggregationsResults);
    }

    protected function extractSearchHit(stdClass $doc, array $languageFilter): SearchHit
    {
        return new SearchHit(
            [
                'score' => $doc->score,
                'index' => $this->getIndexIdentifier($doc),
                'matchedTranslation' => $this->getMatchedLanguageCode($doc),
                'valueObject' => $this->extractHit($doc),
            ]
        );
    }

    protected function extractSpellcheck(stdClass $data, ?Spellcheck $spellcheck): ?SpellcheckResult
    {
        if ($spellcheck === null) {
            return null;
        }

        if (isset($data->spellcheck)) {
            $incorrect = !empty($data->spellcheck->collations);
            $query = $data->spellcheck->collations[1] ?? $spellcheck->getQuery();

            return new SpellcheckResult($query, $incorrect);
        }

        return new SpellcheckResult($spellcheck->getQuery(), false);
    }
}
