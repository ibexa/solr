<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Gateway;

/**
 * Endpoint resolver resolves Solr backend endpoints.
 */
interface EndpointResolver
{
    /**
     * Returns name of the Endpoint used as entry point for distributed search.
     *
     * @return \Ibexa\Solr\Gateway\Endpoint
     */
    public function getEntryEndpoint();

    /**
     * Returns name of the Endpoint that indexes Content translations in the given $languageCode.
     *
     * @param string $languageCode
     *
     * @return string
     */
    public function getIndexingTarget($languageCode);

    /**
     * Returns name of the Endpoint used to index translations in main languages.
     *
     * @return string|null
     */
    public function getMainLanguagesEndpoint();

    /**
     * Returns an array of Endpoint names for the given $languageSettings.
     *
     * @return string[]
     */
    public function getSearchTargets(array $languageSettings);

    /**
     * Returns names of all Endpoints.
     *
     * @return string[]
     */
    public function getEndpoints();
}

class_alias(EndpointResolver::class, 'EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointResolver');
