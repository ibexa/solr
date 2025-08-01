<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace Ibexa\Solr;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;

/**
 * Core filter applies conditions on a query object ensuring matching of correct
 * document across multiple Solr indexes.
 */
abstract class CoreFilter
{
    /**
     * Applies conditions on the $query using given $languageSettings.
     *
     * @param array<string, mixed> $languageSettings
     */
    abstract public function apply(Query $query, array $languageSettings, string $documentTypeIdentifier): void;
}
