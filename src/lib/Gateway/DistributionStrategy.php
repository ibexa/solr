<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Solr\Gateway;

interface DistributionStrategy
{
    /**
     * @param array<string, mixed> $languageSettings
     *
     * @return array<string, mixed>
     */
    public function getSearchParameters(array $parameters, ?array $languageSettings = null): array;
}
