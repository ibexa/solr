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
    protected array $endpoint = [];

    /**
     * Construct from optional array of Endpoints.
     *
     * @param array<string, \Ibexa\Solr\Gateway\Endpoint> $endpoints
     */
    public function __construct(array $endpoints = [])
    {
        foreach ($endpoints as $name => $endpoint) {
            $this->registerEndpoint($name, $endpoint);
        }
    }

    /**
     * Registers $endpoint with $name.
     */
    public function registerEndpoint(string $name, Endpoint $endpoint): void
    {
        $this->endpoint[$name] = $endpoint;
    }

    /**
     * Get Endpoint with $name.
     */
    public function getEndpoint(string $name): Endpoint
    {
        if (!isset($this->endpoint[$name])) {
            throw new OutOfBoundsException("No endpoint registered for '{$name}'.");
        }

        return $this->endpoint[$name];
    }

    /**
     * Gets first Endpoint, for use cases where there is only one.
     */
    public function getFirstEndpoint(): Endpoint
    {
        if (empty($this->endpoint)) {
            throw new OutOfBoundsException("No Endpoint registered at all'.");
        }

        return reset($this->endpoint);
    }
}
