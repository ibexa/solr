<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\Gateway;

use Ibexa\Solr\Gateway;
use OutOfBoundsException;

/**
 * Registry for Solr search engine coreFilters.
 */
final class GatewayRegistry
{
    /** @var \Ibexa\Solr\Gateway[] */
    private $gateways;

    /**
     * @param \Ibexa\Solr\Gateway[] $gateways
     */
    public function __construct(array $gateways = [])
    {
        $this->gateways = $gateways;
    }

    /**
     * @return \Ibexa\Solr\Gateway[]
     */
    public function getGateways(): array
    {
        return $this->gateways;
    }

    /**
     * @param \Ibexa\Solr\Gateway[] $gateways
     */
    public function setGateways(array $gateways): void
    {
        $this->gateways = $gateways;
    }

    public function getGateway(string $connectionName): Gateway
    {
        if (!isset($this->gateways[$connectionName])) {
            throw new OutOfBoundsException(sprintf('No Gateway registered for connection \'%s\'', $connectionName));
        }

        return $this->gateways[$connectionName];
    }

    public function addGateway(string $connectionName, Gateway $gateway): void
    {
        $this->gateways[$connectionName] = $gateway;
    }

    public function hasGateway(string $connectionName): bool
    {
        return isset($this->gateways[$connectionName]);
    }
}
