<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Gateway\DistributionStrategy;

/**
 * Solr Cloud distributed search.
 *
 * @see https://lucene.apache.org/solr/guide/7_7/distributed-requests.html
 */
final class CloudDistributionStrategy extends AbstractDistributionStrategy
{
    private const string COLLECTION_SEPARATOR = ',';
    private const string COLLECTION_PARAMETER = 'collection';

    protected function appendSearchTargets(array $parameters, array $searchTargets): array
    {
        $collections = array_map(fn (string $endpointName) => $this->endpointRegistry->getEndpoint($endpointName)->core, $searchTargets);

        $parameters[self::COLLECTION_PARAMETER] = implode(self::COLLECTION_SEPARATOR, $collections);

        return $parameters;
    }
}
