<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Gateway\DistributionStrategy;

/**
 * Standalone setup of distributed search.
 *
 * @see https://lucene.apache.org/solr/guide/7_7/distributed-search-with-index-sharding.html
 */
final class StandaloneDistributionStrategy extends AbstractDistributionStrategy
{
    private const string SHARD_SEPARATOR = ',';
    private const string SHARD_PARAMETER = 'shards';

    protected function appendSearchTargets(array $parameters, array $searchTargets): array
    {
        $shards = array_map(fn (string $endpointName) => $this->endpointRegistry->getEndpoint($endpointName)->getIdentifier(), $searchTargets);

        $parameters[self::SHARD_PARAMETER] = implode(self::SHARD_SEPARATOR, $shards);

        return $parameters;
    }
}
