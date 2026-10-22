<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Gateway;

use OutOfBoundsException;

/**
 * Registry for Solr search engine Endpoints.
 */
class EndpointRegistry
{
    /**
     * Registered endpoints.
     *
     * @var array<string, Endpoint>
     */
    protected $endpoint = [];

    /**
     * Construct from optional array of Endpoints.
     *
     * @param Endpoint[] $endpoints
     */
    public function __construct(array $endpoints = [])
    {
        foreach ($endpoints as $name => $endpoint) {
            $this->registerEndpoint($name, $endpoint);
        }
    }

    /**
     * Registers $endpoint with $name.
     *
     * @param string $name
     * @param Endpoint $endpoint
     */
    public function registerEndpoint(
        $name,
        Endpoint $endpoint
    ) {
        $this->endpoint[$name] = $endpoint;
    }

    /**
     * Get Endpoint with $name.
     *
     * @param string $name
     *
     * @return Endpoint
     */
    public function getEndpoint($name)
    {
        if (!isset($this->endpoint[$name])) {
            throw new OutOfBoundsException("No endpoint registered for '{$name}'.");
        }

        return $this->endpoint[$name];
    }

    /**
     * Get first Endpoint, for usecases where there is only one.
     *
     * @return Endpoint
     */
    public function getFirstEndpoint()
    {
        if (empty($this->endpoint)) {
            throw new OutOfBoundsException("No Endpoint registered at all'.");
        }

        return reset($this->endpoint);
    }
}

class_alias(EndpointRegistry::class, 'EzSystems\EzPlatformSolrSearchEngine\Gateway\EndpointRegistry');
