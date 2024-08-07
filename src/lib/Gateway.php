<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;

/**
 * The Content Search Gateway provides the implementation for one database to
 * retrieve the desired content objects.
 */
abstract class Gateway
{
    /**
     * Returns search hits for the given query.
     *
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return mixed
     */
    abstract public function findContent(Query $query, array $fieldFilters = []);

    /**
     * Returns search hits for the given query.
     *
     * @param array $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return mixed
     */
    abstract public function findLocations(Query $query, array $fieldFilters = []);

    /**
     * Returns all search hits for given query, that will be performed on all endpoints.
     *
     * @return mixed
     */
    abstract public function searchAllEndpoints(Query $query);

    /**
     * Indexes an array of documents.
     *
     * Documents are given as an array of the array of documents. The array of documents
     * holds documents for all translations of the particular entity.
     *
     * @param \Ibexa\Contracts\Core\Search\Document[][] $documents
     */
    abstract public function bulkIndexDocuments(array $documents);

    /**
     * Deletes documents by the given $query.
     *
     * @param string $query
     */
    abstract public function deleteByQuery($query);

    /**
     * Purges all contents from the index.
     */
    abstract public function purgeIndex();

    /**
     * Commits the data to the Solr index, making it available for search.
     *
     * This will perform Solr 'soft commit', which means there is no guarantee that data
     * is actually written to the stable storage, it is only made available for search.
     * Passing true will also write the data to the safe storage, ensuring durability.
     *
     * @param bool $flush
     */
    abstract public function commit($flush = false);
}
