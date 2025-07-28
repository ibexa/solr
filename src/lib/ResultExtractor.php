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
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Contracts\Solr\ResultExtractor\AggregationResultExtractor;
use Ibexa\Solr\Gateway\EndpointRegistry;
use stdClass;

/**
 * Abstract implementation of Search Extractor, which extracts search result
 * from the data returned by Solr backend.
 */
abstract class ResultExtractor
{
    public function __construct(
        protected AggregationResultExtractor $aggregationResultExtractor,
        protected EndpointRegistry $endpointRegistry
    ) {
    }

    /**
     * Extracts search result from $data returned by Solr backend.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Aggregation[] $aggregations
     * @param array{languages?: string[], languageCode?: string, useAlwaysAvailable?: bool} $languageFilter
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
            $result->searchHits[] = $this->extractSearchHit($doc);
        }

        return $result;
    }

    /**
     * Extracts value object from $hit returned by Solr backend.
     * Needs to be implemented by the concrete ResultExtractor.
     *
     * @param \stdClass{
     *     document_type_id: string,
     *     content_id_id: int|string,
     *     content_name_s: string,
     *     content_type_id_id: int|string,
     *     content_section_id_id: int|string,
     *     content_version_no_i: int,
     *     content_owner_user_id_id: int|string,
     *     content_modification_date_dt: string,
     *     content_publication_date_dt: string,
     *     content_always_available_b: bool,
     *     content_remote_id_id: string,
     *     content_main_language_code_s: string,
     *     main_location_id?: int|string,
     *     location_id: int|string,
     *     priority_i: int,
     *     hidden_b: bool,
     *     invisible_b: bool,
     *     remote_id_id: string,
     *     parent_id_id: int|string,
     *     path_string_id: string,
     *     depth_i: int,
     *     sort_field_id: int|string,
     *     sort_order_id: int|string
     * }&\stdClass $hit
     */
    abstract public function extractHit(stdClass $hit): ValueObject;

    /**
     * Returns language code of the Content's translation of the matched document.
     */
    protected function getMatchedLanguageCode(mixed $hit): string
    {
        return $hit->meta_indexed_language_code_s;
    }

    /**
     * Returns the identifier of the logical index (shard) of the matched document.
     */
    protected function getIndexIdentifier(stdClass $hit): string
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
     * @param array{languages?: string[], languageCode?: string, useAlwaysAvailable?: bool} $languageFilter
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

    protected function extractSearchHit(stdClass $doc): SearchHit
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
