<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\Solr\Query;

use Ibexa\Contracts\Core\Repository\Values\Content\Query;

/**
 * Converts the query tree into an array of Solr query parameters.
 */
abstract class QueryConverter
{
    /**
     * Map query to a proper Solr representation.
     *
     * @phpstan-param array{languages: string[]} $languageSettings
     *
     * @param array $languageSettings - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     *
     * @return array
     */
    abstract public function convert(Query $query, array $languageSettings = []);
}

class_alias(QueryConverter::class, 'EzSystems\EzPlatformSolrSearchEngine\Query\QueryConverter');
