<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Gateway\DistributionStrategy;

use Ibexa\Solr\Gateway\DistributionStrategy;
use Ibexa\Solr\Gateway\EndpointRegistry;
use Ibexa\Solr\Gateway\EndpointResolver;
use Ibexa\Solr\Gateway\SingleEndpointResolver;

abstract class AbstractDistributionStrategy implements DistributionStrategy
{
    /**
     * Endpoint registry service.
     */
    protected EndpointRegistry $endpointRegistry;

    protected EndpointResolver $endpointResolver;

    public function __construct(EndpointRegistry $endpointRegistry, EndpointResolver $endpointResolver)
    {
        $this->endpointRegistry = $endpointRegistry;
        $this->endpointResolver = $endpointResolver;
    }

    public function getSearchParameters(array $parameters, ?array $languageSettings = null): array
    {
        if ($this->endpointResolver instanceof SingleEndpointResolver && !$this->endpointResolver->hasMultipleEndpoints()) {
            return $parameters;
        }

        $searchTargets = $languageSettings !== null ?
            $this->endpointResolver->getSearchTargets($languageSettings) :
            $this->endpointResolver->getEndpoints();

        if (!empty($searchTargets)) {
            $parameters = $this->appendSearchTargets($parameters, $searchTargets);
        }

        return $parameters;
    }

    abstract protected function appendSearchTargets(array $parameters, array $searchTargets): array;
}
