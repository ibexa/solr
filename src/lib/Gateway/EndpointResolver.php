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
     */
    public function getEntryEndpoint(): string;

    /**
     * Returns name of the Endpoint that indexes Content translations in the given $languageCode.
     */
    public function getIndexingTarget(string $languageCode): string;

    /**
     * Returns name of the Endpoint used to index translations in main languages.
     */
    public function getMainLanguagesEndpoint(): ?string;

    /**
     * Returns an array of Endpoint names for the given $languageSettings.
     *
     * @param array<string, mixed> $languageSettings
     *
     * @return list<string>
     */
    public function getSearchTargets(array $languageSettings): array;

    /**
     * Returns names of all Endpoints.
     *
     * @return list<string>
     */
    public function getEndpoints(): array;
}
