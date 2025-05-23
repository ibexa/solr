<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Persistence\Content\Handler as ContentHandler;
use Ibexa\Contracts\Core\Persistence\Content\Location;
use Ibexa\Contracts\Core\Repository\SearchService;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationQuery;
use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion;
use Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField;
use Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult;
use Ibexa\Contracts\Core\Search\VersatileHandler;
use Ibexa\Contracts\Solr\DocumentMapper;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\Base\Exceptions\NotFoundException;

/**
 * The Content Search handler retrieves sets of Content objects, based on a
 * set of criteria.
 *
 * The basic idea of this class is to do the following:
 *
 * 1) The find methods retrieve a recursive set of filters, which define which
 * content objects to retrieve from the database. Those may be combined using
 * boolean operators.
 *
 * 2) This recursive criterion definition is visited into a query, which limits
 * the content retrieved from the database. We might not be able to create
 * sensible queries from all criterion definitions.
 *
 * 3) The query might be possible to optimize (remove empty statements),
 * reduce singular and or constructs…
 *
 * 4) Additionally we might need a post-query filtering step, which filters
 * content objects based on criteria, which could not be converted in to
 * database statements.
 */
class Handler implements VersatileHandler
{
    /* Solr's maxBooleanClauses config value is 1024 */
    public const SOLR_BULK_REMOVE_LIMIT = 1000;
    /* 16b max unsigned integer value due to Solr (JVM) limitations */
    public const SOLR_MAX_QUERY_LIMIT = 65535;
    public const DEFAULT_QUERY_LIMIT = 1000;

    /**
     * Content locator gateway.
     */
    protected Gateway $gateway;

    /**
     * Content handler.
     */
    protected ContentHandler $contentHandler;

    /**
     * Document mapper.
     */
    protected DocumentMapper $mapper;

    /**
     * Content result extractor.
     */
    protected ResultExtractor $contentResultExtractor;

    /**
     * Location result extractor.
     */
    protected ResultExtractor $locationResultExtractor;

    /**
     * Core filter service.
     */
    protected CoreFilter $coreFilter;

    /**
     * Creates a new content handler.
     *
     * @param \Ibexa\Solr\Gateway $gateway
     * @param \Ibexa\Contracts\Solr\DocumentMapper $mapper
     * @param \Ibexa\Solr\ResultExtractor $resultExtractor
     * @param \Ibexa\Solr\CoreFilter $coreFilter
     */
    public function __construct(
        Gateway $gateway,
        ContentHandler $contentHandler,
        DocumentMapper $mapper,
        ResultExtractor $contentResultExtractor,
        ResultExtractor $locationResultExtractor,
        CoreFilter $coreFilter
    ) {
        $this->gateway = $gateway;
        $this->contentHandler = $contentHandler;
        $this->mapper = $mapper;
        $this->contentResultExtractor = $contentResultExtractor;
        $this->locationResultExtractor = $locationResultExtractor;
        $this->coreFilter = $coreFilter;
    }

    public function findContent(Query $query, array $languageFilter = []): SearchResult
    {
        $query = clone $query;
        $query->filter = $query->filter ?: new Criterion\MatchAll();
        $query->query = $query->query ?: new Criterion\MatchAll();

        $this->coreFilter->apply(
            $query,
            $languageFilter,
            DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_CONTENT
        );

        /** @phpstan-var \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult<\Ibexa\Contracts\Core\Persistence\Content\ContentInfo> */
        return $this->contentResultExtractor->extract(
            $this->gateway->findContent($query, $languageFilter),
            $query->aggregations,
            $languageFilter,
            $query->spellcheck
        );
    }

    public function findSingle(Query\CriterionInterface $filter, array $languageFilter = []): Content\ContentInfo
    {
        $query = new Query();
        $query->filter = $filter;
        $query->query = new Criterion\MatchAll();
        $query->offset = 0;
        $query->limit = 1;

        $this->coreFilter->apply(
            $query,
            $languageFilter,
            DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_CONTENT
        );

        /** @phpstan-var \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult<\Ibexa\Contracts\Core\Persistence\Content\ContentInfo> $result */
        $result = $this->contentResultExtractor->extract(
            $this->gateway->findContent($query, $languageFilter)
        );

        if (!$result->totalCount || empty($result->searchHits)) {
            throw new NotFoundException('Content', 'findSingle() found no content for the given $filter');
        }

        if ($result->totalCount > 1) {
            throw new InvalidArgumentException('totalCount', 'findSingle() found more then one Content item for the given $filter');
        }

        return reset($result->searchHits)->valueObject;
    }

    public function findLocations(LocationQuery $query, array $languageFilter = []): SearchResult
    {
        $query = clone $query;
        $query->query = $query->query ?: new Criterion\MatchAll();

        $this->coreFilter->apply(
            $query,
            $languageFilter,
            DocumentMapper::DOCUMENT_TYPE_IDENTIFIER_LOCATION
        );

        /** @phpstan-var \Ibexa\Contracts\Core\Repository\Values\Content\Search\SearchResult<\Ibexa\Contracts\Core\Persistence\Content\Location> */
        return $this->locationResultExtractor->extract(
            $this->gateway->findLocations($query, $languageFilter),
            $query->aggregations,
            $languageFilter,
            $query->spellcheck
        );
    }

    /**
     * Suggests a list of values for the given prefix.
     *
     * @param string $prefix
     * @param string[] $fieldPaths
     * @param int $limit
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion $filter
     */
    public function suggest($prefix, $fieldPaths = [], $limit = 10, Criterion $filter = null)
    {
        throw new \Exception('@todo: Not implemented yet.');
    }

    /**
     * Indexes a content object.
     */
    public function indexContent(Content $content): void
    {
        $this->gateway->bulkIndexDocuments([$this->mapper->mapContentBlock($content)]);
    }

    /**
     * Indexes several content objects.
     *
     * Notes:
     * - Does not force a commit on solr, depends on solr config, use {@see commit()} if you need that.
     * - On large amounts of data make sure to iterate with several calls to this function with a limited
     *   set of content objects, amount you have memory for depends on server, size of objects, & PHP version.
     *
     * @todo: This method & {@see commit()} is needed for being able to bulk index content, and then afterwards commit.
     *       However it is not added to an official SPI interface yet as we anticipate adding a bulkIndexDocument
     *       using Ibexa\Contracts\Core\Search\Document instead of bulkIndexContent based on Content objects. However
     *       that won't be added until we have several stable or close to stable advance search engines to make
     *       sure we match the features of these.
     *       See also {@see Solr\Content\Search\Gateway\Native::bulkIndexContent} for further Solr specific info.
     *
     * @param \Ibexa\Contracts\Core\Persistence\Content[] $contentObjects
     */
    public function bulkIndexContent(array $contentObjects): void
    {
        $documents = [];

        foreach ($contentObjects as $content) {
            try {
                $documents[] = $this->mapper->mapContentBlock($content);
            } catch (NotFoundException $ex) {
                // ignore content objects without assigned state id
            }
        }

        if (!empty($documents)) {
            $this->gateway->bulkIndexDocuments($documents);
        }
    }

    /**
     * Indexes a Location in the index storage.
     */
    public function indexLocation(Location $location): void
    {
        // Does nothing: in this implementation Locations are indexed as
        //               a part of Content document block
    }

    /**
     * Deletes a content object from the index.
     *
     * @param int $contentId
     * @param int|null $versionId
     */
    public function deleteContent($contentId, $versionId = null): void
    {
        $idPrefix = $this->mapper->generateContentDocumentId($contentId);

        $this->gateway->deleteByQuery("_root_:{$idPrefix}*");
    }

    /**
     * Deletes a location from the index.
     *
     * @param mixed $locationId
     * @param mixed $contentId
     */
    public function deleteLocation($locationId, $contentId): void
    {
        $this->deleteAllItemsWithoutAdditionalLocation($locationId);
        $this->updateAllElementsWithAdditionalLocation($locationId);
    }

    /**
     * Purges all contents from the index.
     */
    public function purgeIndex(): void
    {
        $this->gateway->purgeIndex();
    }

    /**
     * Commits the data to the Solr index, making it available for search.
     *
     * This will perform Solr 'soft commit', which means there is no guarantee that data
     * is actually written to the stable storage, it is only made available for search.
     * Passing true will also write the data to the safe storage, ensuring durability.
     *
     * @see bulkIndexContent() For info on why this is not on an SPI Interface yet.
     *
     * @param bool $flush
     */
    public function commit($flush = false): void
    {
        $this->gateway->commit($flush);
    }

    /**
     * @param $locationId
     */
    protected function deleteAllItemsWithoutAdditionalLocation($locationId)
    {
        $query = $this->prepareQuery(self::SOLR_MAX_QUERY_LIMIT);
        $query->filter = new Criterion\LogicalAnd(
            [
                $this->allItemsWithinLocation($locationId),
                new Criterion\LogicalNot($this->allItemsWithinLocationWithAdditionalLocation($locationId)),
            ]
        );

        $searchResult = $this->locationResultExtractor->extract(
            $this->gateway->searchAllEndpoints($query)
        );

        $contentDocumentIds = [];

        foreach ($searchResult->searchHits as $hit) {
            $contentDocumentIds[] = $this->mapper->generateContentDocumentId($hit->valueObject->id) . '*';
        }

        foreach (array_chunk(array_unique($contentDocumentIds), self::SOLR_BULK_REMOVE_LIMIT) as $ids) {
            $query = '_root_:(' . implode(' OR ', $ids) . ')';
            $this->gateway->deleteByQuery($query);
        }
    }

    /**
     * @param $locationId
     */
    protected function updateAllElementsWithAdditionalLocation($locationId)
    {
        $query = $this->prepareQuery(self::SOLR_MAX_QUERY_LIMIT);
        $query->filter = new Criterion\LogicalAnd(
            [
                $this->allItemsWithinLocation($locationId),
                $this->allItemsWithinLocationWithAdditionalLocation($locationId),
            ]
        );

        $searchResult = $this->locationResultExtractor->extract(
            $this->gateway->searchAllEndpoints($query)
        );

        $contentItems = [];
        foreach ($searchResult->searchHits as $searchHit) {
            try {
                $contentInfo = $this->contentHandler->loadContentInfo($searchHit->valueObject->id);
            } catch (NotFoundException $e) {
                continue;
            }

            $contentItems[] = $this->contentHandler->load($contentInfo->id, $contentInfo->currentVersionNo);
        }

        $this->bulkIndexContent($contentItems);
    }

    /**
     * Prepare standard query for delete purpose.
     *
     * @param int $limit
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query
     */
    protected function prepareQuery($limit = self::DEFAULT_QUERY_LIMIT): Query
    {
        return new Query(
            [
                'query' => new Criterion\MatchAll(),
                'limit' => $limit,
                'offset' => 0,
            ]
        );
    }

    /**
     * @param int $locationId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField
     */
    protected function allItemsWithinLocation($locationId): CustomField
    {
        return new CustomField(
            'location_path_string_mid',
            Criterion\Operator::EQ,
            "/.*\\/{$locationId}\\/.*/"
        );
    }

    /**
     * @param int $locationId
     *
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\Query\Criterion\CustomField
     */
    protected function allItemsWithinLocationWithAdditionalLocation($locationId): CustomField
    {
        return new CustomField(
            'location_path_string_mid',
            Criterion\Operator::EQ,
            "/@&~(.*\\/{$locationId}\\/.*)/"
        );
    }

    /**
     * Generate search document for Content object to be indexed by a search engine.
     *
     * @return \Ibexa\Contracts\Core\Search\Document
     */
    public function generateDocument(Content $content)
    {
        return $this->mapper->mapContentBlock($content);
    }

    /**
     * Index search documents generated by generateDocument method.
     *
     * Notes:
     * - Does not force a commit on solr, depends on solr config, use {@see commit()} if you need that.
     * - On large amounts of data make sure to iterate with several calls to this function with a limited
     *   set of content objects, amount you have memory for depends on server, size of objects, & PHP version.
     *
     * @param \Ibexa\Contracts\Core\Search\Document[] $documents
     */
    public function bulkIndexDocuments(array $documents): void
    {
        $this->gateway->bulkIndexDocuments($documents);
    }

    public function supports(int $capabilityFlag): bool
    {
        switch ($capabilityFlag) {
            case SearchService::CAPABILITY_SCORING:
            case SearchService::CAPABILITY_CUSTOM_FIELDS:
            case SearchService::CAPABILITY_SPELLCHECK:
            case SearchService::CAPABILITY_ADVANCED_FULLTEXT:
            case SearchService::CAPABILITY_AGGREGATIONS:
                return true;
            default:
                return false;
        }
    }

    /**
     * Deletes a translation content object from the index.
     */
    public function deleteTranslation(int $contentId, string $languageCode): void
    {
        $this->gateway->deleteByQuery(
            "content_id_id:{$contentId} AND meta_indexed_language_code_s:{$languageCode}"
        );
    }
}
