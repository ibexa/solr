<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr\CoreFilter;

use Ibexa\Solr\CoreFilter;
use OutOfBoundsException;

final class CoreFilterRegistry
{
    /**
     * @param array<string, \Ibexa\Solr\CoreFilter> $coreFilters
     */
    public function __construct(
        private array $coreFilters = []
    ) {
    }

    /**
     * @return array<string, \Ibexa\Solr\CoreFilter> $coreFilters
     */
    public function getCoreFilters(): array
    {
        return $this->coreFilters;
    }

    /**
     * @param array<string, \Ibexa\Solr\CoreFilter> $coreFilters
     */
    public function setCoreFilters(array $coreFilters): void
    {
        $this->coreFilters = $coreFilters;
    }

    public function getCoreFilter(string $connectionName): CoreFilter
    {
        if (!isset($this->coreFilters[$connectionName])) {
            throw new OutOfBoundsException(sprintf('No CoreFilter registered for connection \'%s\'', $connectionName));
        }

        return $this->coreFilters[$connectionName];
    }

    public function addCoreFilter(string $connectionName, CoreFilter $coreFilter): void
    {
        $this->coreFilters[$connectionName] = $coreFilter;
    }

    public function hasCoreFilter(string $connectionName): bool
    {
        return isset($this->coreFilters[$connectionName]);
    }
}
