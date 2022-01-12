<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Gateway;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;
use Ibexa\Contracts\Core\Search\Document;
use Ibexa\Contracts\Core\Search\Field;
use Ibexa\Contracts\Core\Search\FieldType;
use Ibexa\Solr\Gateway;
use Ibexa\Solr\Query\QueryConverter;
use RuntimeException;

/**
 * The Content Search Gateway provides the implementation for one database to
 * retrieve the desired content objects.
 */
class Native extends Gateway
{
    /**
     * HTTP client to communicate with Solr server.
     *
     * @var \Ibexa\Solr\Gateway\HttpClient
     */
    protected $client;

    /**
     * @var \Ibexa\Solr\Gateway\EndpointResolver
     */
    protected $endpointResolver;

    /**
     * Endpoint registry service.
     *
     * @var \Ibexa\Solr\Gateway\EndpointRegistry
     */
    protected $endpointRegistry;

    /**
     * Content Query converter.
     *
     * @var \Ibexa\Solr\Query\QueryConverter
     */
    protected $contentQueryConverter;

    /**
     * Location Query converter.
     *
     * @var \Ibexa\Solr\Query\QueryConverter
     */
    protected $locationQueryConverter;

    /**
     * @var \Ibexa\Solr\Gateway\UpdateSerializer
     */
    protected $updateSerializer;

    /**
     * @var \Ibexa\Solr\Gateway\DistributionStrategy
     */
    protected $distributionStrategy;

    /**
     * @param \Ibexa\Solr\Gateway\HttpClient $client
     * @param \Ibexa\Solr\Gateway\EndpointResolver $endpointResolver
     * @param \Ibexa\Solr\Gateway\EndpointRegistry $endpointRegistry
     * @param \Ibexa\Solr\Gateway\UpdateSerializer $updateSerializer
     * @param \Ibexa\Solr\Gateway\DistributionStrategy $distributionStrategy
     */
    public function __construct(
        HttpClient $client,
        EndpointResolver $endpointResolver,
        EndpointRegistry $endpointRegistry,
        QueryConverter $contentQueryConverter,
        QueryConverter $locationQueryConverter,
        UpdateSerializer $updateSerializer,
        DistributionStrategy $distributionStrategy
    ) {
        $this->client = $client;
        $this->endpointResolver = $endpointResolver;
        $this->endpointRegistry = $endpointRegistry;
        $this->contentQueryConverter = $contentQueryConverter;
        $this->locationQueryConverter = $locationQueryConverter;
        $this->updateSerializer = $updateSerializer;
        $this->distributionStrategy = $distributionStrategy;
    }

    /**
     * Returns search hits for the given query.
     *
     * @param array $languageSettings - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return mixed
     */
    public function findContent(Query $query, array $languageSettings = [])
    {
        $parameters = $this->contentQueryConverter->convert($query, $languageSettings);

        return $this->internalFind($parameters, $languageSettings);
    }

    /**
     * Returns search hits for the given query.
     *
     * @param array $languageSettings - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return mixed
     */
    public function findLocations(Query $query, array $languageSettings = [])
    {
        $parameters = $this->locationQueryConverter->convert($query);

        return $this->internalFind($parameters, $languageSettings);
    }

    /**
     * Returns search hits for the given array of Solr query parameters.
     *
     * @param array $languageSettings - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return mixed
     */
    protected function internalFind(array $parameters, array $languageSettings = [])
    {
        $parameters = $this->distributionStrategy->getSearchParameters($parameters, $languageSettings);

        return $this->search($parameters);
    }

    public function searchAllEndpoints(Query $query)
    {
        $parameters = $this->contentQueryConverter->convert($query);
        $parameters = $this->distributionStrategy->getSearchParameters($parameters);

        return $this->search($parameters);
    }

    /**
     * Generate URL-encoded query string.
     *
     * Array markers, possibly added for the facet parameters,
     * will be removed from the result.
     *
     * @return string
     */
    protected function generateQueryString(array $parameters)
    {
        $removedArrayCharacters = preg_replace(
            '/%5B[0-9]+%5D=/',
            '=',
            http_build_query($parameters)
        );

        $removedDuplicatedEscapingForUrlPath = str_replace('%5C%5C%2F', '%5C%2F', $removedArrayCharacters);

        return $removedDuplicatedEscapingForUrlPath;
    }

    /**
     * Returns search targets for given language settings.
     *
     * Only return endpoints if there are more then one configured, as this is meant for use on shard parameter.
     *
     * @param array $languageSettings
     *
     * @return string
     */
    protected function getSearchTargets($languageSettings)
    {
        if ($this->endpointResolver instanceof SingleEndpointResolver && !$this->endpointResolver->hasMultipleEndpoints()) {
            return '';
        }

        $shards = [];
        $endpoints = $this->endpointResolver->getSearchTargets($languageSettings);

        if (!empty($endpoints)) {
            foreach ($endpoints as $endpoint) {
                $shards[] = $this->endpointRegistry->getEndpoint($endpoint)->getIdentifier();
            }
        }

        return implode(',', $shards);
    }

    /**
     * Returns all search targets without language constraint.
     *
     * Only return endpoints if there are more then one configured, as this is meant for use on shard parameter.
     *
     * @return string
     */
    protected function getAllSearchTargets()
    {
        if ($this->endpointResolver instanceof SingleEndpointResolver && !$this->endpointResolver->hasMultipleEndpoints()) {
            return '';
        }

        $shards = [];
        $searchTargets = $this->endpointResolver->getEndpoints();
        if (!empty($searchTargets)) {
            foreach ($searchTargets as $endpointName) {
                $shards[] = $this->endpointRegistry->getEndpoint($endpointName)->getIdentifier();
            }
        }

        return  implode(',', $shards);
    }

    /**
     * Indexes an array of documents.
     *
     * Documents are given as an array of the array of documents. The array of documents
     * holds documents for all translations of the particular entity.
     *
     * Notes:
     * - Does not force a commit on solr, depends on solr config, use {@commit} if you need that.
     * - On large amounts of data make sure to iterate with several calls to this function with a limited
     *   set of documents, amount you have memory for depends on server, size of documents, & PHP version.
     *
     * @param \Ibexa\Contracts\Core\Search\Document[][] $documents
     */
    public function bulkIndexDocuments(array $documents)
    {
        $documentMap = [];

        $mainTranslationsEndpoint = $this->endpointResolver->getMainLanguagesEndpoint();
        $mainTranslationsDocuments = [];

        foreach ($documents as $translationDocuments) {
            foreach ($translationDocuments as $document) {
                $documentMap[$document->languageCode][] = $document;

                if ($mainTranslationsEndpoint !== null && $document->isMainTranslation) {
                    $mainTranslationsDocuments[] = $this->getMainTranslationDocument($document);
                }
            }
        }

        foreach ($documentMap as $languageCode => $translationDocuments) {
            $this->doBulkIndexDocuments(
                $this->endpointRegistry->getEndpoint(
                    $this->endpointResolver->getIndexingTarget($languageCode)
                ),
                $translationDocuments
            );
        }

        if (!empty($mainTranslationsDocuments)) {
            $this->doBulkIndexDocuments(
                $this->endpointRegistry->getEndpoint($mainTranslationsEndpoint),
                $mainTranslationsDocuments
            );
        }
    }

    /**
     * Returns version of the $document to be indexed in the always available core.
     *
     * @return \Ibexa\Contracts\Core\Search\Document
     */
    protected function getMainTranslationDocument(Document $document)
    {
        // Clone to prevent mutation
        $document = clone $document;
        $subDocuments = [];

        $document->id .= 'mt';
        $document->fields[] = new Field(
            'meta_indexed_main_translation',
            true,
            new FieldType\BooleanField()
        );

        foreach ($document->documents as $subDocument) {
            // Clone to prevent mutation
            $subDocument = clone $subDocument;

            $subDocument->id .= 'mt';
            $subDocument->fields[] = new Field(
                'meta_indexed_main_translation',
                true,
                new FieldType\BooleanField()
            );

            $subDocuments[] = $subDocument;
        }

        $document->documents = $subDocuments;

        return $document;
    }

    /**
     * @param \Ibexa\Solr\Gateway\Endpoint $endpoint
     * @param \Ibexa\Contracts\Core\Search\Document[] $documents
     */
    protected function doBulkIndexDocuments(Endpoint $endpoint, array $documents)
    {
        $updates = $this->updateSerializer->serialize($documents);
        $result = $this->client->request(
            'POST',
            $endpoint,
            '/update?wt=json',
            new Message(
                [
                    'Content-Type' => 'text/xml',
                ],
                $updates
            )
        );

        if ($result->headers['status'] !== 200) {
            throw new RuntimeException('Wrong HTTP status received from Solr: ' . $result->headers['status'] . ' on ' . $endpoint->getURL() . "\n" . var_export($endpoint, true) . "\n" . var_export($result, true) . "\n" . var_export($updates, true));
        }
    }

    /**
     * Deletes documents by the given $query.
     *
     * @param string $query
     */
    public function deleteByQuery($query)
    {
        $endpoints = $this->endpointResolver->getEndpoints();

        foreach ($endpoints as $endpointName) {
            $this->client->request(
                'POST',
                $this->endpointRegistry->getEndpoint($endpointName),
                '/update?wt=json',
                new Message(
                    [
                        'Content-Type' => 'text/xml',
                    ],
                    "<delete><query>{$query}</query></delete>"
                )
            );
        }
    }

    /**
     * @todo implement purging for document type
     *
     * Purges all contents from the index
     */
    public function purgeIndex()
    {
        $endpoints = $this->endpointResolver->getEndpoints();

        foreach ($endpoints as $endpointName) {
            $this->purgeEndpoint(
                $this->endpointRegistry->getEndpoint($endpointName)
            );
        }
    }

    /**
     * @param $endpoint
     *
     * @todo error handling
     */
    protected function purgeEndpoint($endpoint)
    {
        $this->client->request(
            'POST',
            $endpoint,
            '/update?wt=json',
            new Message(
                [
                    'Content-Type' => 'text/xml',
                ],
                '<delete><query>*:*</query></delete>'
            )
        );
    }

    /**
     * Commits the data to the Solr index, making it available for search.
     *
     * This will perform Solr 'soft commit', which means there is no guarantee that data
     * is actually written to the stable storage, it is only made available for search.
     * Passing true will also write the data to the safe storage, ensuring durability.
     *
     * @param bool $flush
     */
    public function commit($flush = false)
    {
        $payload = $flush ?
            '<commit/>' :
            '<commit softCommit="true"/>';

        foreach ($this->endpointResolver->getEndpoints() as $endpointName) {
            $result = $this->client->request(
                'POST',
                $this->endpointRegistry->getEndpoint($endpointName),
                '/update',
                new Message(
                    [
                        'Content-Type' => 'text/xml',
                    ],
                    $payload
                )
            );

            if ($result->headers['status'] !== 200) {
                throw new RuntimeException('Wrong HTTP status received from Solr: ' . $result->headers['status'] . var_export($result, true));
            }
        }
    }

    /**
     * Perform request to client to search for records with query string.
     *
     * @return mixed
     */
    protected function search(array $parameters)
    {
        $queryString = $this->generateQueryString($parameters);

        $response = $this->client->request(
            'POST',
            $this->endpointRegistry->getEndpoint(
                $this->endpointResolver->getEntryEndpoint()
            ),
            '/select',
            new Message(
                [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                $queryString
            )
        );

        // @todo: Error handling?
        $result = json_decode($response->body);

        if (!isset($result->response)) {
            throw new RuntimeException('->response not set: ' . var_export([$result, $parameters], true));
        }

        return $result;
    }
}

class_alias(Native::class, 'EzSystems\EzPlatformSolrSearchEngine\Gateway\Native');
